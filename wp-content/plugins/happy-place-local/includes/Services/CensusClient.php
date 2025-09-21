<?php
namespace HappyPlace\Local\Services;

class CensusClient {
  private string $key; private string $year; private string $dataset;
  public function __construct(string $key, string $year='2023', string $dataset='ACS/acs5') {
    $this->key=$key; $this->year=$year; $this->dataset=$dataset;
  }
  public function placePopulation(string $name, string $stateAbbr): ?int {
    $var = 'B01003_001E';
    $map = ['AL'=>'01','AK'=>'02','AZ'=>'04','AR'=>'05','CA'=>'06','CO'=>'08','CT'=>'09','DE'=>'10','DC'=>'11','FL'=>'12','GA'=>'13','HI'=>'15','ID'=>'16','IL'=>'17','IN'=>'18','IA'=>'19','KS'=>'20','KY'=>'21','LA'=>'22','ME'=>'23','MD'=>'24','MA'=>'25','MI'=>'26','MN'=>'27','MS'=>'28','MO'=>'29','MT'=>'30','NE'=>'31','NV'=>'32','NH'=>'33','NJ'=>'34','NM'=>'35','NY'=>'36','NC'=>'37','ND'=>'38','OH'=>'39','OK'=>'40','OR'=>'41','PA'=>'42','RI'=>'44','SC'=>'45','SD'=>'46','TN'=>'47','TX'=>'48','UT'=>'49','VT'=>'50','VA'=>'51','WA'=>'53','WV'=>'54','WI'=>'55','WY'=>'56'];
    $fips = $map[$stateAbbr] ?? null; if (!$fips) return null;
    $url = "https://api.census.gov/data/{$this->year}/acs/acs5?get=NAME,{$var}&for=place:*&in=state:{$fips}&key={$this->key}";
    $r = wp_remote_get($url, ['timeout'=>20]);
    if (is_wp_error($r)) return null;
    $rows = json_decode(wp_remote_retrieve_body($r), true) ?: [];
    foreach (array_slice($rows,1) as $row) {
      if (stripos($row[0], $name) !== false) return (int)$row[1];
    }
    return null;
  }
}
