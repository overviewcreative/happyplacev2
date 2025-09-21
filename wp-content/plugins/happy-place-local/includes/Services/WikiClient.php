<?php
namespace HappyPlace\Local\Services;

class WikiClient {
  private string $lang;
  public function __construct(string $lang='en') { $this->lang = $lang; }

  public function summary(string $title, string $location = ''): array {
    // If location is provided, try location-specific search first
    if ($location) {
      $locationSpecific = $this->findLocationSpecificPage($title, $location);
      if ($locationSpecific) {
        return $this->getSummaryByTitle($locationSpecific);
      }
    }
    
    // Fallback to original title
    return $this->getSummaryByTitle($title);
  }

  private function getSummaryByTitle(string $title): array {
    $url = "https://{$this->lang}.wikipedia.org/api/rest_v1/page/summary/" . rawurlencode($title);
    $r = wp_remote_get($url, ['timeout'=>20]);
    if (is_wp_error($r)) return [];
    $d = json_decode(wp_remote_retrieve_body($r), true) ?: [];
    return [
      'extract' => $d['extract'] ?? '',
      'image'   => $d['thumbnail']['source'] ?? '',
      'wikidata'=> $d['wikidata_id'] ?? '',
      'title'   => $d['title'] ?? $title
    ];
  }

  private function findLocationSpecificPage(string $cityName, string $location): ?string {
    $searchTerms = [
      "{$cityName}, {$location}",
      "{$cityName} ({$location})",
      "{$cityName}, {$this->getFullStateName($location)}",
      "{$cityName} ({$this->getFullStateName($location)})"
    ];

    foreach ($searchTerms as $searchTerm) {
      $result = $this->searchWikipedia($searchTerm);
      if ($result) {
        // Verify this is actually about the right location
        if ($this->isCorrectLocation($result, $cityName, $location)) {
          return $result;
        }
      }
    }

    return null;
  }

  private function searchWikipedia(string $query): ?string {
    $url = "https://{$this->lang}.wikipedia.org/w/api.php";
    $params = [
      'action' => 'query',
      'format' => 'json',
      'list' => 'search',
      'srsearch' => $query,
      'srlimit' => 3
    ];
    
    $r = wp_remote_get(add_query_arg($params, $url), ['timeout' => 20]);
    if (is_wp_error($r)) return null;
    
    $data = json_decode(wp_remote_retrieve_body($r), true);
    $results = $data['query']['search'] ?? [];
    
    return !empty($results) ? $results[0]['title'] : null;
  }

  private function isCorrectLocation(string $pageTitle, string $cityName, string $stateAbbr): bool {
    // Get page content to verify location
    $url = "https://{$this->lang}.wikipedia.org/w/api.php";
    $params = [
      'action' => 'query',
      'format' => 'json',
      'prop' => 'extracts',
      'exintro' => true,
      'explaintext' => true,
      'titles' => $pageTitle
    ];
    
    $r = wp_remote_get(add_query_arg($params, $url), ['timeout' => 20]);
    if (is_wp_error($r)) return false;
    
    $data = json_decode(wp_remote_retrieve_body($r), true);
    $pages = $data['query']['pages'] ?? [];
    
    foreach ($pages as $page) {
      $extract = strtolower($page['extract'] ?? '');
      $stateName = strtolower($this->getFullStateName($stateAbbr));
      
      // Check if the extract mentions the state
      if (strpos($extract, strtolower($stateAbbr)) !== false || 
          strpos($extract, $stateName) !== false) {
        return true;
      }
    }
    
    return false;
  }

  private function getFullStateName(string $abbreviation): string {
    $stateMap = [
      'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
      'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
      'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
      'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
      'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
      'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
      'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
      'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
      'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
      'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
      'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
      'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
      'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia'
    ];
    
    return $stateMap[$abbreviation] ?? $abbreviation;
  }

  public function wikidataPopulation(string $wikidataId): ?int {
    if (!$wikidataId) return null;
    $sparql = 'SELECT ?pop WHERE { wd:' . $wikidataId . ' wdt:P1082 ?pop . } ORDER BY DESC(?pop) LIMIT 1';
    $url = add_query_arg(['query'=>$sparql, 'format'=>'json'], 'https://query.wikidata.org/sparql');
    $r = wp_remote_get($url, ['timeout'=>20, 'headers'=>['User-Agent'=>'HappyPlaceLocal/1.0']]);
    if (is_wp_error($r)) return null;
    $d = json_decode(wp_remote_retrieve_body($r), true) ?: [];
    return isset($d['results']['bindings'][0]['pop']['value']) ? (int)$d['results']['bindings'][0]['pop']['value'] : null;
  }
}
