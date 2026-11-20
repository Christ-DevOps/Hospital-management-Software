<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="NexClinic — sector-based hospital operations for patients, pharmacy, laboratory, staff, and finance.">
    <meta name="theme-color" content="#2E37A4">
    <title>NexClinic</title>
    <link rel="shortcut icon" href="assets/images/logo/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/animate-3.7.0.css">
    <link rel="stylesheet" href="assets/css/font-awesome-4.7.0.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-4.1.3.min.css">
    <link rel="stylesheet" href="assets/css/owl-carousel.min.css">
    <link rel="stylesheet" href="assets/css/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hms-landing.css">
</head>
<body>
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <header class="header-area">
        <div id="header">
            <div class="container">
                <div class="row align-items-center justify-content-between d-flex">
                    <div id="logo">
                        <a href="index.php" class="hms-logo" aria-label="NexClinic home">
                            <img src="assets/images/feature1.png" width="36" height="36" alt="">
                            <span class="hms-logo-text">Nex<span>Clinic</span></span>
                        </a>
                    </div>
                    <nav id="nav-menu-container" aria-label="Primary">
                        <ul class="nav-menu">
                            <li class="menu-active"><a href="#home">Home</a></li>
                            <li><a href="#sectors">Sectors</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#platform">Platform</a></li>
                            <li><a href="#roles">Access</a></li>
                            <li><a href="backend/doc/index.php">Staff Login</a></li>
                            <li class="nav-cta"><a href="backend/admin/index.php">Admin Login</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <main id="home">
        <section class="banner-area hms-hero" aria-labelledby="brand-title">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <p class="hms-brand-mark" data-hms-stagger style="animation-delay:0ms" id="brand-title">NexClinic</p>
                        <h1 class="hms-hero-title" data-hms-stagger style="animation-delay:90ms">
                            Hospital work, organized by sector
                        </h1>
                        <p class="hms-hero-lead" data-hms-stagger style="animation-delay:180ms">
                            Reception, clinical care, pharmacy, lab, and operations — each team sees only its work, on one shared patient spine.
                        </p>
                        <div class="hms-hero-actions" data-hms-stagger style="animation-delay:270ms">
                            <a href="backend/admin/index.php" class="btn btn-primary btn-hms-primary">Administrator Login</a>
                            <a href="backend/doc/index.php" class="btn btn-success btn-hms-ghost">Staff Login</a>
                        </div>
                    </div>
                    <div class="col-lg-6 hms-hero-visual" data-hms-stagger style="animation-delay:360ms">
                        <div class="hms-device hms-device-float" aria-hidden="true">
                            <div class="hms-device-chrome">
                                <i></i><i></i><i></i>
                                <div class="url">nexclinic.local / admin / dashboard</div>
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
            </div>
        </section>

        <section id="sectors" class="hms-section" aria-labelledby="sectors-title">
            <div class="container">
                <div class="hms-section-head hms-reveal">
                    <p class="hms-eyebrow">Sectors</p>
                    <h2 id="sectors-title">Clear boundaries for every team</h2>
                    <p>Each role works inside its sector. The patient record is the shared spine — many can read it, few can write it.</p>
                </div>
                <div class="hms-grid-3">
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-desktop"></i></div>
                        <h3>Reception</h3>
                        <p>Register patients, book appointments, and run the day’s check-in queue.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-stethoscope"></i></div>
                        <h3>Clinical</h3>
                        <p>Notes, prescriptions, lab orders, and admission — authored by the caring clinician.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-amber" aria-hidden="true"><i class="fa fa-medkit"></i></div>
                        <h3>Pharmacy</h3>
                        <p>Catalogue, stock integrity, and dispensing against doctor prescriptions.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-purple" aria-hidden="true"><i class="fa fa-flask"></i></div>
                        <h3>Laboratory</h3>
                        <p>Process requests and record results against the correct patient.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-line-chart"></i></div>
                        <h3>Accounting</h3>
                        <p>Invoices, payments, and payable/receivable with numeric amounts.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-users"></i></div>
                        <h3>HR &amp; Payroll</h3>
                        <p>Staff records, attendance, leave, and salary runs — separate from clinical care.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="features" class="hms-section hms-section-alt" aria-labelledby="features-title">
            <div class="container">
                <div class="hms-section-head hms-reveal">
                    <p class="hms-eyebrow">Why NexClinic</p>
                    <h2 id="features-title">Built for Cameroonian hospital operations</h2>
                    <p>Proper registration fields, sector access, and audited writes — from front desk through clinical care.</p>
                </div>
                <div class="hms-grid-4">
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-id-card"></i></div>
                        <h3>Cameroon registration</h3>
                        <p>Sex, blood group, emergency contacts, village/division fields on every new patient.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-calendar"></i></div>
                        <h3>Real appointments</h3>
                        <p>Book, reschedule, complete, or cancel — with a live day schedule for staff.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-amber" aria-hidden="true"><i class="fa fa-shield"></i></div>
                        <h3>Two-axis authority</h3>
                        <p>Platform tier (superadmin / admin / standard) plus sector assignment and rank.</p>
                    </article>
                    <article class="hms-card hms-reveal">
                        <div class="hms-icon tint-purple" aria-hidden="true"><i class="fa fa-lock"></i></div>
                        <h3>Secure by default</h3>
                        <p>CSRF, prepared statements, bcrypt, and session hardening on every portal.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="platform" class="hms-section" aria-labelledby="platform-title">
            <div class="container">
                <div class="hms-preview-row">
                    <div class="hms-preview-copy hms-reveal">
                        <p class="hms-eyebrow">Platform</p>
                        <h2 id="platform-title">One shell. Sector-focused workspaces.</h2>
                        <p>Administrators oversee the hospital. Clinicians and sector staff open only the modules they are assigned to.</p>
                        <ul class="hms-preview-list">
                            <li>Shared patient spine — demographics written by reception, clinical by doctors</li>
                            <li>Pharmacy and lab close the care loop from order to result/dispense</li>
                            <li>Consistent NexClinic design from landing through dashboards</li>
                        </ul>
                        <a href="backend/admin/index.php" class="btn btn-hms-primary">Open admin portal</a>
                    </div>
                    <div class="hms-reveal" aria-hidden="true">
                        <div class="hms-device">
                            <div class="hms-device-chrome">
                                <i></i><i></i><i></i>
                                <div class="url">nexclinic.local / clinical / patients</div>
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

        <section id="roles" class="hms-section hms-section-alt" aria-labelledby="roles-title">
            <div class="container">
                <div class="hms-section-head hms-reveal">
                    <p class="hms-eyebrow">Access</p>
                    <h2 id="roles-title">Sign in to your portal</h2>
                    <p>Hospital admins run operations. Clinical and sector staff run the floor.</p>
                </div>
                <div class="hms-roles">
                    <article class="hms-role-card hms-reveal">
                        <div class="hms-icon tint-blue" aria-hidden="true"><i class="fa fa-sitemap"></i></div>
                        <h3>Administrators</h3>
                        <p>Staff accounts, cross-sector oversight, patients, appointments, pharmacy, lab, payroll, and finance.</p>
                        <a href="backend/admin/index.php" class="btn btn-hms-primary">Administrator Login</a>
                    </article>
                    <article class="hms-role-card hms-reveal">
                        <div class="hms-icon tint-green" aria-hidden="true"><i class="fa fa-user-md"></i></div>
                        <h3>Clinical &amp; sector staff</h3>
                        <p>Doctors and assigned staff: patients, appointments, prescriptions, labs, and sector workflows.</p>
                        <a href="backend/doc/index.php" class="btn btn-hms-ghost">Staff Login</a>
                    </article>
                </div>
            </div>
        </section>

        <section class="hms-cta-band text-center" aria-labelledby="cta-title">
            <div class="container hms-reveal">
                <h2 id="cta-title">Ready for clearer hospital operations?</h2>
                <p>Sign in to NexClinic with the portal that matches your role.</p>
                <div class="hms-hero-actions justify-content-center">
                    <a href="backend/admin/index.php" class="btn btn-hms-primary">Get started — Admin</a>
                    <a href="backend/doc/index.php" class="btn btn-hms-ghost">Staff Login</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="hms-footer" id="contact">
        <div class="container">
            <div class="hms-footer-grid">
                <div class="hms-footer-brand">
                    <a href="index.php" class="hms-logo" aria-label="NexClinic home">
                        <img src="assets/images/feature1.png" width="36" height="36" alt="">
                        <span class="hms-logo-text">Nex<span>Clinic</span></span>
                    </a>
                    <p>Sector-based hospital management for Cameroonian facilities — patients, clinical care, pharmacy, lab, and operations.</p>
                </div>
                <div>
                    <h4>Navigate</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#sectors">Sectors</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#platform">Platform</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Portals</h4>
                    <ul>
                        <li><a href="backend/admin/index.php">Administrator Login</a></li>
                        <li><a href="backend/doc/index.php">Staff Login</a></li>
                        <li><a href="#roles">Access overview</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="mailto:admin@mail.com">admin@mail.com</a></li>
                        <li><span style="color:rgba(255,255,255,0.72);">NexClinic</span></li>
                    </ul>
                </div>
            </div>
            <div class="hms-footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> NexClinic. All rights reserved.</span>
                <span>Clinical dashboards · sector workspaces</span>
            </div>
        </div>
    </footer>

    <script src="assets/js/vendor/jquery-2.2.4.min.js"></script>
    <script src="assets/js/vendor/bootstrap-4.1.3.min.js"></script>
    <script src="assets/js/vendor/wow.min.js"></script>
    <script src="assets/js/vendor/owl-carousel.min.js"></script>
    <script src="assets/js/vendor/jquery.datetimepicker.full.min.js"></script>
    <script src="assets/js/vendor/jquery.nice-select.min.js"></script>
    <script src="assets/js/vendor/superfish.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/hms-motion.js"></script>
</body>
</html>
