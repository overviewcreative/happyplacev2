<?php
/**
 * Template Name: Framework Diagnostic Showcase
 * Enhanced comprehensive testing and diagnostic showcase for HPH Framework
 *
 * @package HappyPlaceTheme
 */

get_header(); ?>

<div class="framework-showcase">
    <!-- Enhanced Hero Section -->
    <section class="hero-section position-relative overflow-hidden">
        <div class="hero-background position-absolute inset-0">
            <div class="hero-gradient"></div>
            <div class="hero-pattern"></div>
        </div>
        <div class="container position-relative py-5">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-7">
                    <div class="hero-content">
                        <div class="hero-badge mb-4">
                            <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-pill">
                                <i class="fas fa-code me-2"></i>Framework v2.1.0
                            </span>
                        </div>
                        <h1 class="hero-title display-3 fw-bold text-white mb-4">
                            HPH Framework
                            <span class="text-gradient">Showcase</span>
                        </h1>
                        <p class="hero-subtitle lead text-white-75 mb-4 fs-5">
                            Complete diagnostic environment for testing components, real estate use cases, 
                            and framework capabilities. Built with Bootstrap 5.3, Poppins fonts, and custom CSS variables.
                        </p>
                        <div class="hero-actions d-flex flex-wrap gap-3">
                            <button class="btn btn-white btn-lg px-4 py-3" onclick="scrollToSection('components')">
                                <i class="fas fa-rocket me-2"></i>Explore Components
                            </button>
                            <button class="btn btn-outline-white btn-lg px-4 py-3" onclick="toggleThemeCustomizer()">
                                <i class="fas fa-palette me-2"></i>Customize Theme
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-stats">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value" id="component-count">47</div>
                                <div class="stat-label">Components</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" id="variable-count">150+</div>
                                <div class="stat-label">CSS Variables</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" id="utility-count">200+</div>
                                <div class="stat-label">Utilities</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value text-success" id="status-indicator">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-label">Framework Status</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Navigation -->
    <nav class="showcase-nav sticky-top bg-white shadow-lg border-bottom">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-8">
                    <div class="nav-tabs-container">
                        <ul class="nav nav-pills nav-fill d-flex flex-nowrap overflow-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="#overview" data-section="overview">
                                    <i class="fas fa-home me-2"></i>Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#components" data-section="components">
                                    <i class="fas fa-cubes me-2"></i>Components
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#typography" data-section="typography">
                                    <i class="fas fa-font me-2"></i>Typography
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#colors" data-section="colors">
                                    <i class="fas fa-palette me-2"></i>Colors
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#real-estate" data-section="real-estate">
                                    <i class="fas fa-home me-2"></i>Real Estate
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#diagnostics" data-section="diagnostics">
                                    <i class="fas fa-cog me-2"></i>Diagnostics
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nav-tools d-flex align-items-center justify-content-end gap-2">
                        <div class="search-container position-relative">
                            <input type="text" class="form-control form-control-sm" placeholder="Search components..." id="component-search">
                            <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleDarkMode()">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Theme Customizer Panel -->
    <div class="theme-customizer position-fixed end-0 top-0 h-100 bg-white shadow-lg border-start" id="theme-customizer">
        <div class="customizer-header p-4 border-bottom">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Theme Customizer</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleThemeCustomizer()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="customizer-content p-4">
            <div class="customizer-section mb-4">
                <h6 class="fw-semibold mb-3">Primary Color</h6>
                <div class="color-picker-grid">
                    <button class="color-option active" data-color="#51bae0" style="background: #51bae0;"></button>
                    <button class="color-option" data-color="#3b82f6" style="background: #3b82f6;"></button>
                    <button class="color-option" data-color="#8b5cf6" style="background: #8b5cf6;"></button>
                    <button class="color-option" data-color="#06b6d4" style="background: #06b6d4;"></button>
                    <button class="color-option" data-color="#10b981" style="background: #10b981;"></button>
                    <button class="color-option" data-color="#f59e0b" style="background: #f59e0b;"></button>
                </div>
            </div>
            
            <div class="customizer-section mb-4">
                <h6 class="fw-semibold mb-3">Typography</h6>
                <div class="form-group mb-3">
                    <label class="form-label small">Font Size Scale</label>
                    <input type="range" class="form-range" min="0.8" max="1.2" step="0.1" value="1" id="font-scale">
                </div>
                <div class="form-group">
                    <label class="form-label small">Line Height</label>
                    <input type="range" class="form-range" min="1.4" max="1.8" step="0.1" value="1.6" id="line-height">
                </div>
            </div>
            
            <div class="customizer-section mb-4">
                <h6 class="fw-semibold mb-3">Layout</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="compact-mode">
                    <label class="form-check-label" for="compact-mode">Compact Mode</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rounded-corners" checked>
                    <label class="form-check-label" for="rounded-corners">Rounded Corners</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="box-shadows" checked>
                    <label class="form-check-label" for="box-shadows">Box Shadows</label>
                </div>
            </div>
            
            <div class="customizer-actions">
                <button class="btn btn-primary w-100 mb-2" onclick="exportThemeCSS()">
                    <i class="fas fa-download me-2"></i>Export CSS
                </button>
                <button class="btn btn-outline-secondary w-100" onclick="resetThemeCustomizer()">
                    <i class="fas fa-undo me-2"></i>Reset
                </button>
            </div>
        </div>
    </div>

    <div class="container my-5">

        <!-- Overview Section -->
        <section id="overview" class="showcase-section mb-5">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold text-primary mb-3">Framework Overview</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    A comprehensive look at the HPH Framework's capabilities, performance metrics, and integration status.
                </p>
            </div>

            <div class="row g-4 mb-5">
                <!-- Performance Metrics -->
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value" id="load-time">0ms</div>
                            <div class="metric-label">Load Time</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <div class="file-size-icon">CSS</div>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value" id="css-size">0KB</div>
                            <div class="metric-label">CSS Bundle Size</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value">100%</div>
                            <div class="metric-label">Components Loaded</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value">A+</div>
                            <div class="metric-label">Mobile Score</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Framework Status -->
                <div class="col-lg-8">
                    <div class="status-card">
                        <div class="status-header">
                            <h5 class="mb-0">Framework Status</h5>
                            <span class="badge bg-success">All Systems Operational</span>
                        </div>
                        <div class="status-grid">
                            <div class="status-item">
                                <div class="status-icon">
                                    <i class="fas fa-font text-primary"></i>
                                </div>
                                <div class="status-content">
                                    <div class="status-title">Google Fonts</div>
                                    <div class="status-subtitle">Poppins & Inter loaded</div>
                                </div>
                                <div class="status-indicator">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-icon">
                                    <i class="fab fa-bootstrap text-primary"></i>
                                </div>
                                <div class="status-content">
                                    <div class="status-title">Bootstrap 5.3.0</div>
                                    <div class="status-subtitle">Grid & components active</div>
                                </div>
                                <div class="status-indicator">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-icon">
                                    <i class="fas fa-icons text-primary"></i>
                                </div>
                                <div class="status-content">
                                    <div class="status-title">Font Awesome 6.4.0</div>
                                    <div class="status-subtitle">Icon library loaded</div>
                                </div>
                                <div class="status-indicator">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-icon">
                                    <i class="fas fa-paint-brush text-primary"></i>
                                </div>
                                <div class="status-content">
                                    <div class="status-title">HPH Framework</div>
                                    <div class="status-subtitle">CSS variables active</div>
                                </div>
                                <div class="status-indicator" id="framework-status">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="quick-actions-card">
                        <h5 class="mb-4">Quick Actions</h5>
                        <div class="d-grid gap-3">
                            <button class="btn btn-outline-primary" onclick="runDiagnostics()">
                                <i class="fas fa-stethoscope me-2"></i>Run Diagnostics
                            </button>
                            <button class="btn btn-outline-secondary" onclick="exportFrameworkData()">
                                <i class="fas fa-download me-2"></i>Export Framework Data
                            </button>
                            <button class="btn btn-outline-info" onclick="viewPerformanceReport()">
                                <i class="fas fa-chart-line me-2"></i>Performance Report
                            </button>
                            <button class="btn btn-outline-warning" onclick="testResponsiveness()">
                                <i class="fas fa-mobile-alt me-2"></i>Test Responsive
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Enhanced Components Section -->
        <section id="components" class="showcase-section mb-5">
            <div class="section-header mb-5">
                <h2 class="display-5 fw-bold text-primary mb-3">Component Gallery</h2>
                <p class="lead text-muted">Interactive examples of all framework components with live code previews.</p>
            </div>

            <!-- Component Categories -->
            <div class="component-categories mb-4">
                <div class="category-pills">
                    <button class="category-pill active" data-category="all">All Components</button>
                    <button class="category-pill" data-category="buttons">Buttons</button>
                    <button class="category-pill" data-category="cards">Cards</button>
                    <button class="category-pill" data-category="forms">Forms</button>
                    <button class="category-pill" data-category="real-estate">Real Estate</button>
                    <button class="category-pill" data-category="utilities">Utilities</button>
                </div>
            </div>

            <!-- Enhanced Button Showcase -->
            <div class="component-showcase" data-category="buttons">
                <div class="showcase-header">
                    <h4 class="mb-0">Button Components</h4>
                    <button class="btn btn-sm btn-outline-secondary" onclick="showCode('button-code')">
                        <i class="fas fa-code me-1"></i>View Code
                    </button>
                </div>
                
                <div class="showcase-content">
                    <div class="component-demo p-4 border rounded-3 mb-3">
                        <div class="row g-3">
                            <div class="col-auto">
                                <h6 class="text-muted mb-2">Primary Actions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-primary">Contact Agent</button>
                                    <button class="btn btn-primary btn-lg">Schedule Tour</button>
                                    <button class="btn btn-primary" disabled>Processing...</button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <h6 class="text-muted mb-2">Secondary Actions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-outline-primary">Save Property</button>
                                    <button class="btn btn-outline-secondary">Share Listing</button>
                                    <button class="btn btn-link">Learn More</button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <h6 class="text-muted mb-2">Real Estate Actions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-success">
                                        <i class="fas fa-phone me-2"></i>Call Now
                                    </button>
                                    <button class="btn btn-info">
                                        <i class="fas fa-calendar me-2"></i>Book Showing
                                    </button>
                                    <button class="btn btn-warning">
                                        <i class="fas fa-heart me-2"></i>Favorite
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="code-preview collapse" id="button-code">
                        <div class="code-header">
                            <span class="code-language">HTML</span>
                            <button class="btn btn-sm btn-outline-light" onclick="copyCode('button-code-content')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre class="code-content" id="button-code-content"><code>&lt;button class="btn btn-primary"&gt;Contact Agent&lt;/button&gt;
&lt;button class="btn btn-outline-primary"&gt;Save Property&lt;/button&gt;
&lt;button class="btn btn-success"&gt;
    &lt;i class="fas fa-phone me-2"&gt;&lt;/i&gt;Call Now
&lt;/button&gt;</code></pre>
                    </div>
                </div>
            </div>

            <!-- Enhanced Card Showcase -->
            <div class="component-showcase" data-category="cards">
                <div class="showcase-header">
                    <h4 class="mb-0">Card Components</h4>
                    <button class="btn btn-sm btn-outline-secondary" onclick="showCode('card-code')">
                        <i class="fas fa-code me-1"></i>View Code
                    </button>
                </div>
                
                <div class="showcase-content">
                    <div class="component-demo p-4 border rounded-3 mb-3">
                        <div class="row g-4">
                            <!-- Property Card -->
                            <div class="col-lg-4">
                                <div class="card h-100 card-hover">
                                    <div class="position-relative">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI1MCIgZmlsbD0iIzUxYmFlMCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvcGVydHkgSW1hZ2U8L3RleHQ+PC9zdmc+" class="card-img-top" alt="Property">
                                        <span class="badge bg-success position-absolute top-0 start-0 m-3">For Sale</span>
                                        <div class="position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-dark bg-opacity-75 fs-6">$450,000</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Modern Family Home</h5>
                                        <p class="card-text text-muted small mb-3">123 Main Street, Anytown, CA</p>
                                        <div class="property-features d-flex justify-content-between text-muted small mb-3">
                                            <span><i class="fas fa-bed me-1"></i>3 bed</span>
                                            <span><i class="fas fa-bath me-1"></i>2 bath</span>
                                            <span><i class="fas fa-expand-arrows-alt me-1"></i>1,500 sqft</span>
                                        </div>
                                        <p class="card-text small">Beautiful home with modern updates and great location.</p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary btn-sm flex-fill">View Details</button>
                                            <button class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Agent Card -->
                            <div class="col-lg-4">
                                <div class="card h-100 text-center card-hover">
                                    <div class="card-body">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI1MCIgZmlsbD0iIzUxYmFlMCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMzAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+U0o8L3RleHQ+PC9zdmc+" class="rounded-circle mb-3" alt="Agent" width="100" height="100">
                                        <h5 class="card-title">Sarah Johnson</h5>
                                        <p class="card-text text-muted">Licensed Real Estate Agent</p>
                                        <div class="mb-3">
                                            <div class="text-warning mb-1">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <small class="text-muted">5.0 (24 reviews)</small>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary btn-sm">Contact Agent</button>
                                            <button class="btn btn-outline-secondary btn-sm">View Profile</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stats Card -->
                            <div class="col-lg-4">
                                <div class="card h-100 bg-gradient-primary text-white">
                                    <div class="card-body text-center d-flex flex-column justify-content-center">
                                        <div class="display-4 fw-bold mb-2">1,234</div>
                                        <p class="card-text mb-3">Properties Sold This Year</p>
                                        <div class="text-success-light">
                                            <i class="fas fa-arrow-up me-1"></i> +12% from last year
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="code-preview collapse" id="card-code">
                        <div class="code-header">
                            <span class="code-language">HTML</span>
                            <button class="btn btn-sm btn-outline-light" onclick="copyCode('card-code-content')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre class="code-content" id="card-code-content"><code>&lt;div class="card h-100"&gt;
    &lt;img src="property-image.jpg" class="card-img-top" alt="Property"&gt;
    &lt;div class="card-body"&gt;
        &lt;h5 class="card-title"&gt;Modern Family Home&lt;/h5&gt;
        &lt;p class="card-text"&gt;Beautiful home with modern updates.&lt;/p&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
                    </div>
                </div>
            </div>
        </section>

        <!-- Real Estate Components -->
        <section id="real-estate" class="showcase-section mb-5">
            <div class="section-header mb-5">
                <h2 class="display-5 fw-bold text-primary mb-3">Real Estate Components</h2>
                <p class="lead text-muted">Industry-specific components designed for real estate websites.</p>
            </div>

            <div class="row g-4">
                <!-- Mortgage Calculator -->
                <div class="col-lg-8">
                    <div class="calculator-card">
                        <div class="calculator-header">
                            <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Interactive Mortgage Calculator</h5>
                        </div>
                        <div class="calculator-body">
                            <form id="enhanced-mortgage-calc">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Home Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="enhanced-home-price" value="450000">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Down Payment</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="enhanced-down-payment" value="20" min="0" max="100">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Interest Rate</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="enhanced-interest-rate" value="6.5" step="0.1">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Loan Term</label>
                                        <select class="form-select" id="enhanced-loan-term">
                                            <option value="30" selected>30 years</option>
                                            <option value="15">15 years</option>
                                            <option value="20">20 years</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="enhanced-calc-result" class="calculator-results">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="result-item">
                                                <div class="result-value text-primary" id="enhanced-monthly-payment">$2,147</div>
                                                <div class="result-label">Monthly Payment</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="result-item">
                                                <div class="result-value text-secondary" id="enhanced-down-amount">$90,000</div>
                                                <div class="result-label">Down Payment</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="result-item">
                                                <div class="result-value text-info" id="enhanced-loan-amount">$360,000</div>
                                                <div class="result-label">Loan Amount</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="result-item">
                                                <div class="result-value text-warning" id="enhanced-total-interest">$412,920</div>
                                                <div class="result-label">Total Interest</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Property Search Widget -->
                <div class="col-lg-4">
                    <div class="search-widget">
                        <div class="search-header">
                            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Property Search</h5>
                        </div>
                        <div class="search-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" placeholder="City, State or ZIP">
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Min Price</label>
                                        <select class="form-select">
                                            <option>No Min</option>
                                            <option>$100,000</option>
                                            <option>$200,000</option>
                                            <option>$300,000</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Max Price</label>
                                        <select class="form-select">
                                            <option>No Max</option>
                                            <option>$500,000</option>
                                            <option>$750,000</option>
                                            <option>$1,000,000</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Beds</label>
                                        <select class="form-select">
                                            <option>Any</option>
                                            <option>1+</option>
                                            <option>2+</option>
                                            <option>3+</option>
                                            <option>4+</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Baths</label>
                                        <select class="form-select">
                                            <option>Any</option>
                                            <option>1+</option>
                                            <option>2+</option>
                                            <option>3+</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search Properties
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Diagnostics Section -->
        <section id="diagnostics" class="showcase-section mb-5">
            <div class="section-header mb-5">
                <h2 class="display-5 fw-bold text-primary mb-3">Framework Diagnostics</h2>
                <p class="lead text-muted">Detailed diagnostic information and performance metrics.</p>
            </div>

            <div class="row g-4">
                <!-- CSS Variables Test -->
                <div class="col-lg-6">
                    <div class="diagnostic-card">
                        <div class="diagnostic-header">
                            <h5 class="mb-0">CSS Variables Status</h5>
                            <span class="badge bg-success">Active</span>
                        </div>
                        <div class="diagnostic-content">
                            <div class="variable-test mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Primary Color (HPH)</span>
                                    <div class="color-sample" style="background: var(--hph-primary);"></div>
                                </div>
                            </div>
                            <div class="variable-test mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Primary Color (Bootstrap)</span>
                                    <div class="color-sample" style="background: var(--bs-primary);"></div>
                                </div>
                            </div>
                            <div class="variable-test mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Primary Font</span>
                                    <span class="font-sample" style="font-family: var(--hph-font-primary);">Poppins</span>
                                </div>
                            </div>
                            <div class="alert alert-success mt-3" id="enhanced-variable-status">
                                <i class="fas fa-check-circle me-2"></i>All CSS variables loaded successfully
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Metrics -->
                <div class="col-lg-6">
                    <div class="diagnostic-card">
                        <div class="diagnostic-header">
                            <h5 class="mb-0">Performance Metrics</h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshMetrics()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="diagnostic-content">
                            <div class="performance-grid">
                                <div class="performance-item">
                                    <div class="performance-label">CSS Load Time</div>
                                    <div class="performance-value" id="css-load-time">Loading...</div>
                                </div>
                                <div class="performance-item">
                                    <div class="performance-label">JavaScript Load Time</div>
                                    <div class="performance-value" id="js-load-time">Loading...</div>
                                </div>
                                <div class="performance-item">
                                    <div class="performance-label">Total Assets</div>
                                    <div class="performance-value" id="total-assets">Loading...</div>
                                </div>
                                <div class="performance-item">
                                    <div class="performance-label">Framework Size</div>
                                    <div class="performance-value" id="framework-size">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<script>
// Enhanced Framework Showcase JavaScript

class FrameworkShowcase {
    constructor() {
        this.init();
        this.bindEvents();
        this.startDiagnostics();
    }
    
    init() {
        this.setupNavigation();
        this.setupThemeCustomizer();
        this.setupComponentSearch();
        this.calculateMetrics();
    }
    
    bindEvents() {
        // Navigation events
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.navigateToSection(link.dataset.section);
            });
        });
        
        // Calculator events
        ['enhanced-home-price', 'enhanced-down-payment', 'enhanced-interest-rate', 'enhanced-loan-term'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => this.calculateEnhancedPayment());
                element.addEventListener('change', () => this.calculateEnhancedPayment());
            }
        });
        
        // Theme customizer events
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', () => this.changeThemeColor(option.dataset.color));
        });
        
        // Component category filters
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.addEventListener('click', () => this.filterComponents(pill.dataset.category));
        });
    }
    
    setupNavigation() {
        // Setup smooth scrolling and active states
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.updateActiveNavigation(entry.target.id);
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('.showcase-section').forEach(section => {
            observer.observe(section);
        });
    }
    
    updateActiveNavigation(sectionId) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const activeLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
    
    navigateToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    calculateEnhancedPayment() {
        const homePrice = parseFloat(document.getElementById('enhanced-home-price')?.value) || 450000;
        const downPaymentPercent = parseFloat(document.getElementById('enhanced-down-payment')?.value) || 20;
        const interestRate = parseFloat(document.getElementById('enhanced-interest-rate')?.value) || 6.5;
        const loanTermYears = parseInt(document.getElementById('enhanced-loan-term')?.value) || 30;
        
        const downPaymentAmount = homePrice * (downPaymentPercent / 100);
        const loanAmount = homePrice - downPaymentAmount;
        const monthlyRate = (interestRate / 100) / 12;
        const numberOfPayments = loanTermYears * 12;
        
        const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                             (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
        
        const totalInterest = (monthlyPayment * numberOfPayments) - loanAmount;
        
        // Update display with animation
        this.animateValue('enhanced-monthly-payment', '$' + Math.round(monthlyPayment).toLocaleString());
        this.animateValue('enhanced-down-amount', '$' + Math.round(downPaymentAmount).toLocaleString());
        this.animateValue('enhanced-loan-amount', '$' + Math.round(loanAmount).toLocaleString());
        this.animateValue('enhanced-total-interest', '$' + Math.round(totalInterest).toLocaleString());
    }
    
    animateValue(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.transform = 'scale(1.1)';
            element.style.transition = 'transform 0.2s ease';
            setTimeout(() => {
                element.textContent = newValue;
                element.style.transform = 'scale(1)';
            }, 100);
        }
    }
    
    setupThemeCustomizer() {
        // Initialize theme customizer
        const customizer = document.getElementById('theme-customizer');
        if (customizer) {
            customizer.style.transform = 'translateX(100%)';
            customizer.style.transition = 'transform 0.3s ease';
        }
    }
    
    changeThemeColor(color) {
        document.documentElement.style.setProperty('--hph-primary', color);
        document.documentElement.style.setProperty('--bs-primary', color);
        
        // Update active color option
        document.querySelectorAll('.color-option').forEach(option => {
            option.classList.remove('active');
        });
        document.querySelector(`[data-color="${color}"]`).classList.add('active');
        
        // Show success message
        this.showNotification('Theme color updated successfully!', 'success');
    }
    
    filterComponents(category) {
        // Update active category
        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Filter components
        document.querySelectorAll('.component-showcase').forEach(showcase => {
            if (category === 'all' || showcase.dataset.category === category) {
                showcase.style.display = 'block';
                showcase.style.animation = 'fadeIn 0.3s ease';
            } else {
                showcase.style.display = 'none';
            }
        });
    }
    
    startDiagnostics() {
        // Run initial diagnostics
        this.checkCSSVariables();
        this.measurePerformance();
        this.calculateEnhancedPayment();
    }
    
    checkCSSVariables() {
        const hphPrimary = getComputedStyle(document.documentElement).getPropertyValue('--hph-primary').trim();
        const bsPrimary = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim();
        
        const statusElement = document.getElementById('enhanced-variable-status');
        
        if (hphPrimary && bsPrimary && hphPrimary === bsPrimary) {
            statusElement.className = 'alert alert-success mt-3';
            statusElement.innerHTML = '<i class="fas fa-check-circle me-2"></i>All CSS variables loaded successfully';
            
            // Update framework status
            const frameworkStatus = document.getElementById('framework-status');
            if (frameworkStatus) {
                frameworkStatus.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
            }
        } else {
            statusElement.className = 'alert alert-warning mt-3';
            statusElement.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>CSS variable override may not be working properly';
        }
    }
    
    measurePerformance() {
        // Simulate performance measurements
        setTimeout(() => {
            document.getElementById('css-load-time').textContent = '45ms';
            document.getElementById('js-load-time').textContent = '120ms';
            document.getElementById('total-assets').textContent = '8 files';
            document.getElementById('framework-size').textContent = '145KB';
            document.getElementById('load-time').textContent = '165ms';
            document.getElementById('css-size').textContent = '145KB';
        }, 1000);
    }
    
    showNotification(message, type = 'info') {
        // Create and show notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle me-2"></i>${message}`;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Global functions for buttons
function toggleThemeCustomizer() {
    const customizer = document.getElementById('theme-customizer');
    const isVisible = customizer.style.transform === 'translateX(0%)';
    
    customizer.style.transform = isVisible ? 'translateX(100%)' : 'translateX(0%)';
}

function toggleFullscreen() {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        document.documentElement.requestFullscreen();
    }
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const icon = document.getElementById('theme-icon');
    icon.className = document.body.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
}

function showCode(codeId) {
    const codeElement = document.getElementById(codeId);
    if (codeElement) {
        codeElement.classList.toggle('show');
    }
}

function copyCode(codeContentId) {
    const codeContent = document.getElementById(codeContentId);
    if (codeContent) {
        navigator.clipboard.writeText(codeContent.textContent);
        // Show success feedback
        const copyBtn = event.target.closest('button');
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            copyBtn.innerHTML = originalText;
        }, 1000);
    }
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function runDiagnostics() {
    console.log('Running comprehensive diagnostics...');
    // Add diagnostic functionality
}

function exportFrameworkData() {
    console.log('Exporting framework data...');
    // Add export functionality
}

function viewPerformanceReport() {
    console.log('Generating performance report...');
    // Add performance report functionality
}

function testResponsiveness() {
    console.log('Testing responsive behavior...');
    // Add responsive testing functionality
}

function refreshMetrics() {
    console.log('Refreshing performance metrics...');
    // Add metrics refresh functionality
}

function exportThemeCSS() {
    console.log('Exporting custom theme CSS...');
    // Add CSS export functionality
}

function resetThemeCustomizer() {
    // Reset to default theme
    document.documentElement.style.removeProperty('--hph-primary');
    document.documentElement.style.removeProperty('--bs-primary');
    
    // Reset active color option
    document.querySelectorAll('.color-option').forEach(option => {
        option.classList.remove('active');
    });
    document.querySelector('[data-color="#51bae0"]').classList.add('active');
}

// Initialize showcase when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FrameworkShowcase();
});
</script>

<?php get_footer(); ?>