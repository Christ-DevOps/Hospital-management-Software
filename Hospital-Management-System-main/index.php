<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Hospital Management System — manage patients, pharmacy, laboratory, staff, and finance from one modern clinical platform.">
    <meta name="theme-color" content="#2E37A4">
    <title>Hospital Management System</title>
    <link rel="shortcut icon" href="assets/images/logo/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/animate-3.7.0.css">
    <link rel="stylesheet" href="assets/css/font-awesome-4.7.0.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-4.1.3.min.css">
    <link rel="stylesheet" href="assets/css/owl-carousel.min.css">
    <link rel="stylesheet" href="assets/css/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hms-landing.css">
</head>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <!-- Header -->
    <header class="header-area">
        <div id="header">
            <div class="container">
                <div class="row align-items-center justify-content-between d-flex">
                    <div id="logo">
                        <a href="index.php" class="hms-logo" aria-label="Hospital Management System home">
                            <img src="assets/images/feature1.png" width="36" height="36" alt="">
                            <span class="hms-logo-text">H<span>MS</span></span>
                        </a>
                    </div>
                    <nav id="nav-menu-container" aria-label="Primary">
                        <ul class="nav-menu">
                            <li class="menu-active"><a href="#home">Home</a></li>
                            <li><a href="#departments">Departments</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#platform">Platform</a></li>
                            <li><a href="#roles">Access</a></li>
                            <li><a href="backend/doc/index.php">Doctor Login</a></li>
                            <li class="nav-cta"><a href="backend/admin/index.php">Admin Login</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <main id="home">
        <!-- Hero -->
        <section class="banner-area hms-hero" aria-labelledby="hero-title">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <p class="hms-eyebrow" data-hms-stagger style="animation-delay:0ms">Clinic management made simple</p>
                        <h1 id="hero-title" class="hms-hero-title" data-hms-stagger style="animation-delay:90ms">
                            Run your hospital from one clear workspace
                        </h1>
                        <p class="hms-hero-lead" data-hms-stagger style="animation-delay:180ms">
                            Patients, pharmacy, laboratory, staff, and finance — coordinated in a single platform built for clinical clarity.
                        </p>
                        <div class="hms-hero-actions" data-hms-stagger style="animation-delay:270ms">
                            <a href="backend/admin/index.php" class="btn btn-primary btn-hms-primary">Administrator Login</a>
                            <a href="backend/doc/index.php" class="btn btn-success btn-hms-ghost">Doctor Login</a>
                        </div>
                    </div>
                    <div class="col-lg-6 hms-hero-visual" data-hms-stagger style="animation-delay:360ms">
                        <div class="hms-device hms-device-float" aria-hidden="true">
                            <div class="hms-device-chrome">
                                <i></i><i></i><i></i>
                                <div class="url">hms.local / admin / dashboard</div>
                            </div>
                            <div class="hms-device-body">
                                <div class="hms-mock-sidebar">
                                    <span class="active"></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="hms-mock-main">
                                    <div class="hms-mock-top"></div>
                                    <div class="hms-mock-stats">
                                        <div class="hms-mock-stat">
                                            <div class="dot b"></div>
                                            <div class="line w"></div>
                                            <div class="line"></div>
                                        </div>
                                        <div class="hms-mock-stat">
                                            <div class="dot g"></div>
                                            <div class="line w"></div>
                                            <div class="line"></div>
                                        </div>
                                        <div class="hms-mock-stat">
                                            <div class="dot a"></div>
                                            <div class="line w"></div>
                                            <div class="line"></div>
                                        </div>
                                        <div class="hms-mock-stat">
                                            <div class="dot p"></div>
                                            <div class="line w"></div>
                                            <div class="line"></div>
                                        </div>
                                    </div>
                                    <div class="hms-mock-chart">
                                        <b style="height:40%"></b>
                                        <b style="height:55%"></b>
                                        <b style="height:80%"></b>
                                        <b style="height:45%"></b>
                                        <b style="height:65%"></b>
                                        <b style="height:90%"></b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trust bar -->
                <div class="hms-trust" data-hms-stagger style="animation-delay:480ms">
                    <div class="hms-trust-grid">
                        <div class="hms-trust-item">
                            <div class="num"><span data-hms-counter data-hms-suffix="+">500</span><span>+</span></div>
                            <div class="label">Hospitals &amp; clinics</div>
                        </div>
                        <div class="hms-trust-item">
                            <div class="num"><span data-hms-counter>10</span><span>k+</span></div>
                            <div class="label">Clinicians onboarded</div>
                        </div>
                        <div class="hms-trust-item">
                            <div class="num"><span data-hms-counter>1</span><span>M+</span></div>
                            <div class="label">Patients managed</div>
                        </div>
                        <div class="hms-trust-item">
                            <div class="num"><span data-hms-counter data-hms-decimals="1">99.9</span><span>%</span></div>
                            <div class="label">Platform uptime</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Departments -->
        <section id="departments" class="hms-section" aria-labelledby="dept-title">
            <div class="container">
                <div class="hms-section-head hms-reveal">
                    <p class="hms-eyebrow">Departments</p>
                    <h2 id="dept-title">Care pathways your teams already know</h2>
                    <p>Organize clinical work around real hospital departments — from cardiology to laboratory — without switching tools.</p>
                </div>
                <div class="hms-grid-3">
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-heartbeat"></i></div>
                        <h3>Cardiology</h3>
                        <p>Track cardiac patients, vitals, and follow-ups alongside shared medical records.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-purple" aria-hidden="true"><i class="fa fa-hospital-o"></i></div>
                        <h3>Neurology</h3>
                        <p>Coordinate assessments, lab work, and prescriptions for neurological care.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-child"></i></div>
                        <h3>Pediatrics</h3>
                        <p>Manage younger patients with age-aware records and outpatient workflows.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-amber" aria-hidden="true"><i class="fa fa-flask"></i></div>
                        <h3>Laboratory</h3>
                        <p>Order tests, capture results, and keep clinicians in the loop from one queue.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-medkit"></i></div>
                        <h3>Pharmacy</h3>
                        <p>Categories, stock, and prescriptions tied directly to patient care plans.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-purple" aria-hidden="true"><i class="fa fa-user-md"></i></div>
                        <h3>Surgery &amp; Theatre</h3>
                        <p>Schedule theatre cases and keep surgical records with the rest of the chart.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="hms-section hms-section-alt" aria-labelledby="features-title">
            <div class="container">
                <div class="hms-section-head hms-reveal">
                    <p class="hms-eyebrow">Why HMS</p>
                    <h2 id="features-title">Everything operations and clinicians share</h2>
                    <p>One design system from the public site through admin and doctor dashboards — so teams learn the product once.</p>
                </div>
                <div class="hms-grid-4">
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-folder-open"></i></div>
                        <h3>Unified records</h3>
                        <p>Patient demographics, ailments, transfers, and history in one place.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-calendar"></i></div>
                        <h3>Clinical workflows</h3>
                        <p>Inpatient and outpatient flows, vitals, labs, and prescriptions connected.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-amber" aria-hidden="true"><i class="fa fa-users"></i></div>
                        <h3>Staff &amp; payroll</h3>
                        <p>Employee directories, assignments, and payroll generation for administrators.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-purple" aria-hidden="true"><i class="fa fa-lock"></i></div>
                        <h3>Secure access</h3>
                        <p>Role-separated admin and doctor portals with session-based authentication.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Platform preview -->
        <section id="platform" class="hms-section" aria-labelledby="platform-title">
            <div class="container">
                <div class="hms-preview-row">
                    <div class="hms-preview-copy hms-reveal">
                        <p class="hms-eyebrow">Platform</p>
                        <h2 id="platform-title">A dashboard your staff will actually use</h2>
                        <p>The same indigo-blue SaaS shell powers administrator and doctor views — stats, tables, and workflows without visual whiplash.</p>
                        <ul class="hms-preview-list">
                            <li>Live patient, pharmacy, and lab metrics on the admin home</li>
                            <li>Doctor workspace focused on patients, prescriptions, and labs</li>
                            <li>Shared typography, cards, and buttons across every screen</li>
                        </ul>
                        <a href="backend/admin/index.php" class="btn btn-hms-primary">Open admin portal</a>
                    </div>
                    <div class="hms-reveal" aria-hidden="true">
                        <div class="hms-device">
                            <div class="hms-device-chrome">
                                <i></i><i></i><i></i>
                                <div class="url">hms.local / doctor / patients</div>
                            </div>
                            <div class="hms-device-body">
                                <div class="hms-mock-sidebar">
                                    <span></span>
                                    <span class="active"></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="hms-mock-main">
                                    <div class="hms-mock-top" style="width:55%"></div>
                                    <div class="hms-mock-stats">
                                        <div class="hms-mock-stat">
                                            <div class="dot g"></div>
                                            <div class="line w"></div>
                                            <div class="line"></div>
                                        </div>
                                        <div class="hms-mock-stat">
                                            <div class="dot a"></div>
                                            <div class="line w"></div>
                                            <div class="line"></div>
                                        </div>
                                    </div>
                                    <div class="hms-mock-chart">
                                        <b style="height:50%"></b>
                                        <b style="height:70%"></b>
                                        <b style="height:40%"></b>
                                        <b style="height:85%"></b>
                                        <b style="height:60%"></b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Roles (replaces doctor photo showcase — no real staff photos available) -->
        <section id="roles" class="hms-section hms-section-alt" aria-labelledby="roles-title">
            <div class="container">
                <div class="hms-section-head hms-reveal">
                    <p class="hms-eyebrow">Access</p>
                    <h2 id="roles-title">Built for two sides of the hospital</h2>
                    <p>Administrators run the institution. Doctors run the floor. Each portal stays focused.</p>
                </div>
                <div class="hms-roles">
                    <article class="hms-role-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-sitemap"></i></div>
                        <h3>Administrators</h3>
                        <p>Patients, employees, pharmacy inventory, laboratory assets, payroll, vendors, and accounting — full operational control.</p>
                        <a href="backend/admin/index.php" class="btn btn-hms-primary">Administrator Login</a>
                    </article>
                    <article class="hms-role-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-stethoscope"></i></div>
                        <h3>Doctors</h3>
                        <p>Register and manage patients, write prescriptions, work lab results and vitals, and view your own payroll history.</p>
                        <a href="backend/doc/index.php" class="btn btn-hms-ghost">Doctor Login</a>
                    </article>
                </div>
            </div>
        </section>

        <!-- CTA band -->
        <section class="hms-cta-band text-center" aria-labelledby="cta-title">
            <div class="container hms-reveal">
                <h2 id="cta-title">Ready to manage care with clarity?</h2>
                <p>Sign in to the portal that matches your role and pick up where your team left off.</p>
                <div class="hms-hero-actions justify-content-center">
                    <a href="backend/admin/index.php" class="btn btn-hms-primary">Get started — Admin</a>
                    <a href="backend/doc/index.php" class="btn btn-hms-ghost">Doctor Login</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="hms-footer" id="contact">
        <div class="container">
            <div class="hms-footer-grid">
                <div class="hms-footer-brand">
                    <a href="index.php" class="hms-logo" aria-label="Hospital Management System home">
                        <img src="assets/images/feature1.png" width="36" height="36" alt="">
                        <span class="hms-logo-text">H<span>MS</span></span>
                    </a>
                    <p>A modern hospital management platform for patients, pharmacy, laboratory, staff, and finance.</p>
                    <div class="hms-footer-social">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fa fa-twitter"></i></a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fa fa-linkedin"></i></a>
                    </div>
                </div>
                <div>
                    <h4>Navigate</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#departments">Departments</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#platform">Platform</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Portals</h4>
                    <ul>
                        <li><a href="backend/admin/index.php">Administrator Login</a></li>
                        <li><a href="backend/doc/index.php">Doctor Login</a></li>
                        <li><a href="#roles">Access overview</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="mailto:admin@mail.com">admin@mail.com</a></li>
                        <li><span style="color:rgba(255,255,255,0.72);">Hospital Management System</span></li>
                        <li><span style="color:rgba(255,255,255,0.5);">Support via your local deployment</span></li>
                    </ul>
                </div>
            </div>
            <div class="hms-footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> Hospital Management System. All rights reserved.</span>
                <span>Designed to match the HMS clinical dashboards.</span>
            </div>
        </div>
    </footer>

    <script src="assets/js/vendor/jquery-2.2.4.min.js"></script>
    <script src="assets/js/vendor/bootstrap-4.1.3.min.js"></script>
    <!-- WOW kept only because main.js calls new WOW().init(); landing reveals use hms-motion IntersectionObserver -->
    <script src="assets/js/vendor/wow.min.js"></script>
    <script src="assets/js/vendor/owl-carousel.min.js"></script>
    <script src="assets/js/vendor/jquery.datetimepicker.full.min.js"></script>
    <script src="assets/js/vendor/jquery.nice-select.min.js"></script>
    <script src="assets/js/vendor/superfish.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/hms-motion.js"></script>
</body>
</html>
