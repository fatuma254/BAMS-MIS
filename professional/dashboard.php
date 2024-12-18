
      <?php 
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');

      
      
      
      
      ?>
          <div class="content-wrapper">
          <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAMS Beauty - Professional Beauty Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6469df;
            --secondary: #37ca32;
            --dark: #2c2e33;
            --light: #f2f2f2;
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../assets/images/hero.webp');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .feature-card {
            transition: transform 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }

        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stats-box {
            padding: 40px 20px;
            text-align: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .navbar {
            background: rgba(255,255,255,0.95) !important;
        }

        .cta-section {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
        }
    </style>
</head>
<body>
   
    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container text-white text-center">
            <h1 class="display-3 fw-bold mb-4">Transform Your Beauty Business</h1>
            <p class="lead mb-4">Streamline your beauty agency operations with our comprehensive management system</p>
            <button class="btn btn-primary btn-lg px-5 me-3">Explore</button>
            <button class="btn btn-outline-light btn-lg px-5">Learn More</button>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose BAMS?</h2>
                <p class="lead text-muted">Powerful features to transform your beauty business</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <img src="../assets/images/a2.webp" class="card-img-top" alt="Scheduling">
                        <div class="card-body text-center">
                            <h4>Smart Scheduling</h4>
                            <p>Effortlessly manage appointments and staff schedules</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <img src="../assets/images/a1.webp" class="card-img-top" alt="Client Management">
                        <div class="card-body text-center">
                            <h4>Client Management</h4>
                            <p>Track client history and preferences seamlessly</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <img src="../assets/images/a3.webp" class="card-img-top" alt="Financial Tracking">
                        <div class="card-body text-center">
                            <h4>Financial Tracking</h4>
                            <p>Comprehensive financial management and reporting</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="bg-light py-5" id="services">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Services</h2>
                <p class="lead text-muted">Comprehensive solutions for beauty professionals</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4>Appointment Booking</h4>
                        <p>Streamlined booking system with automated reminders</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Staff Management</h4>
                        <p>Efficient staff scheduling and performance tracking</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center">
                        <div class="service-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Business Analytics</h4>
                        <p>Detailed insights and reporting for your business</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5" id="testimonials">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">What Our Clients Say</h2>
                <p class="lead text-muted">Success stories from beauty professionals</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <img src="../assets/images/sarah.avif" width="100%" class="rounded-circle mb-3" alt="Testimonial">
                        <p class="mb-3">"BAMS has revolutionized how we manage our salon. The scheduling system is a game-changer!"</p>
                        <h5>Sarah Johnson</h5>
                        <p class="text-muted">Salon Owner</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <img src="../assets/images/mike.avif" class="rounded-circle mb-3" width="100%" alt="Testimonial">
                        <p class="mb-3">"The financial tracking features have helped us increase our revenue by 40% in just 3 months."</p>
                        <h5>Michael Chen</h5>
                        <p class="text-muted">Spa Manager</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <img src="../assets/images/emma.jpg"  width="100%"  class="rounded-circle mb-3" alt="Testimonial">
                        <p class="mb-3">"Customer management has never been easier. Our clients love the booking experience!"</p>
                        <h5>Emma Davis</h5>
                        <p class="text-muted">Beauty Consultant</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-box">
                        <h3 class="display-4 fw-bold text-primary">500+</h3>
                        <p class="text-muted">Active Salons</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-box">
                        <h3 class="display-4 fw-bold text-primary">50K+</h3>
                        <p class="text-muted">Appointments</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-box">
                        <h3 class="display-4 fw-bold text-primary">98%</h3>
                        <p class="text-muted">Satisfaction Rate</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-box">
                        <h3 class="display-4 fw-bold text-primary">24/7</h3>
                        <p class="text-muted">Support</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Beauty Business?</h2>
            <p class="lead mb-4">Join thousands of successful beauty professionals using BAMS</p>
            <button class="btn btn-light btn-lg px-5">Get Started Today</button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5>About BAMS</h5>
                    <p>Professional beauty management system designed to streamline your operations and boost your business growth.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light">Features</a></li>
                        <li><a href="#" class="text-light">Pricing</a></li>
                        <li><a href="#" class="text-light">Resources</a></li>
                        <li><a href="#" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> info@bamsbeauty.com</li>
                        <li><i class="fas fa-phone me-2"></i> +254 712 563 676</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Banyara Street, Eco Towers</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>Computerised Beauty with BAMS. Welcome!!</p>
            </div>
        </div>
    </footer>

</body>
</html>
          </div>
          <?php  include('../includes/footer.php'); ?>