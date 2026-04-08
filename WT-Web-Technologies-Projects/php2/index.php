<?php
$dbHost = '127.0.0.1';
$dbName = 'portfolio_db';
$dbUser = 'root';
$dbPass = 'roshan jatu';

$profile     = null;
$skills      = [];
$projects    = [];
$internships = [];
$formMsg     = '';
$formError   = false;
$dbConnected = false;

function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $dbConnected = true;

    // Handle contact form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'contact') {
        $name    = trim(filter_input(INPUT_POST, 'sender_name',  FILTER_DEFAULT) ?? '');
        $email   = trim(filter_input(INPUT_POST, 'sender_email', FILTER_VALIDATE_EMAIL) ?? '');
        $message = trim(filter_input(INPUT_POST, 'message',      FILTER_DEFAULT) ?? '');

        if ($name === '' || $email === '' || $message === '') {
            $formError = true;
            $formMsg   = 'Please fill in all fields correctly.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (sender_name, sender_email, message) VALUES (:name, :email, :message)'
            );
            $stmt->execute(['name' => $name, 'email' => $email, 'message' => $message]);
            $formMsg = 'Thank you! Your message has been sent.';
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($formMsg) . '&err=' . ($formError ? '1' : '0') . '#contact');
        exit;
    }

    if (!empty($_GET['msg'])) {
        $formMsg   = (string) $_GET['msg'];
        $formError = ($_GET['err'] ?? '0') === '1';
    }

    $profile     = $pdo->query('SELECT * FROM profile LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $skills      = $pdo->query('SELECT name FROM skills ORDER BY sort_order ASC')->fetchAll(PDO::FETCH_COLUMN);
    $projects    = $pdo->query('SELECT title, description FROM projects ORDER BY sort_order ASC')->fetchAll(PDO::FETCH_ASSOC);
    $internships = $pdo->query('SELECT company, role, description FROM internships ORDER BY sort_order ASC')->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $ex) {
    // Fallback static data when DB is unavailable
    if (!empty($_GET['msg'])) {
        $formMsg   = (string) $_GET['msg'];
        $formError = ($_GET['err'] ?? '0') === '1';
    }

    $profile = [
        'full_name'    => 'Monil Patel',
        'tagline'      => 'Web & Android Developer | CSE Student',
        'bio'          => 'Passionate Computer Science student with strong foundation in OOP, Data Structures, DBMS and scalable application design. I build clean, modern and user-friendly web & Android applications.',
        'email'        => 'monilpatel154@gmail.com',
        'phone'        => '8849740412',
        'location'     => 'Bengaluru, India',
        'linkedin_url' => 'https://www.linkedin.com/in/monil-patel-946845255/',
        'github_url'   => 'https://github.com/Monilpatel154',
    ];
    $skills      = ['JavaScript','TypeScript','Kotlin','Python','Next.js','HTML','CSS','PostgreSQL','Git','Power BI'];
    $projects    = [
        ['title' => 'Multi-Transportation Android App', 'description' => 'Modular Android application with structured navigation and reusable components.'],
        ['title' => 'Student Dashboard - PostgreSQL',   'description' => 'Designed optimized database schema with efficient SQL queries.'],
        ['title' => 'Socialz Web Platform',             'description' => 'Responsive frontend built using TypeScript and Tailwind CSS.'],
    ];
    $internships = [
        ['company' => 'RTsense',          'role' => 'Web Development Intern',  'description' => 'Improved UI, fixed production bugs and enhanced system performance.'],
        ['company' => 'Microsoft Elevate','role' => 'Power BI Internship',     'description' => 'Built KPI dashboards and generated business insights.'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($profile['full_name']); ?> | Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav>
        <a href="#about">About</a>
        <a href="#skills">Skills</a>
        <a href="#projects">Projects</a>
        <a href="#internships">Experience</a>
        <a href="#contact">Contact</a>
    </nav>

    <header>
        <div class="hero-badge">Open to opportunities</div>
        <h1><?php echo e($profile['full_name']); ?></h1>
        <p><?php echo e($profile['tagline']); ?></p>
        <div class="hero-cta">
            <a href="#projects" class="btn-primary">View Projects</a>
            <a href="#contact"  class="btn-outline">Get in Touch</a>
        </div>
        <div class="scroll-hint">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            scroll
        </div>
    </header>

    <div class="divider"></div>

    <section id="about" class="section">
        <div class="about-grid">
            <div class="about-img-wrap">
                <img src="monil.jpg.jpeg" alt="<?php echo e($profile['full_name']); ?>">
            </div>
            <div class="about-text">
                <span class="section-label">About Me</span>
                <h2>Building things for the web &amp; mobile</h2>
                <p><?php echo e($profile['bio']); ?></p>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <section id="skills" class="section">
        <span class="section-label">What I know</span>
        <h2>Technical Skills</h2>
        <div class="skills-grid">
            <?php foreach ($skills as $skill): ?>
                <span class="skill-pill"><?php echo e($skill); ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="divider"></div>

    <section id="projects" class="section">
        <span class="section-label">What I've built</span>
        <h2>Projects</h2>
        <div class="cards-grid">
            <?php foreach ($projects as $project): ?>
                <div class="card">
                    <h3><?php echo e($project['title']); ?></h3>
                    <p><?php echo e($project['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="divider"></div>

    <section id="internships" class="section">
        <span class="section-label">Where I've worked</span>
        <h2>Internship Experience</h2>
        <div class="timeline">
            <?php foreach ($internships as $intern): ?>
                <div class="timeline-item">
                    <div class="role"><?php echo e($intern['role']); ?></div>
                    <h3><?php echo e($intern['company']); ?></h3>
                    <p><?php echo e($intern['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="divider"></div>

    <section id="contact" class="section">
        <div class="contact-grid">
            <div class="contact-info">
                <span class="section-label">Get in touch</span>
                <h2>Let's work together</h2>
                <div class="contact-detail"><strong>Email</strong></div>
                <div class="contact-detail"><?php echo e($profile['email']); ?></div>
                <div class="contact-detail" style="margin-top:8px"><strong>Phone</strong></div>
                <div class="contact-detail"><?php echo e($profile['phone']); ?></div>
                <div class="contact-detail" style="margin-top:8px"><strong>Location</strong></div>
                <div class="contact-detail"><?php echo e($profile['location']); ?></div>
                <div class="social-links">
                    <a href="<?php echo e($profile['linkedin_url']); ?>" target="_blank" rel="noopener">LinkedIn</a>
                    <a href="<?php echo e($profile['github_url']); ?>"   target="_blank" rel="noopener">GitHub</a>
                </div>
            </div>

            <div class="contact-form">
                <h3>Send a Message</h3>
                <?php if (!empty($formMsg)): ?>
                    <div class="form-notice <?php echo $formError ? 'error' : 'success'; ?>"><?php echo e($formMsg); ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="action" value="contact">
                    <div class="form-group">
                        <label for="f_name">Name</label>
                        <input id="f_name" type="text" name="sender_name" placeholder="Monil Patel" required>
                    </div>
                    <div class="form-group">
                        <label for="f_email">Email</label>
                        <input id="f_email" type="email" name="sender_email" placeholder="hello@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="f_msg">Message</label>
                        <textarea id="f_msg" name="message" rows="5" placeholder="Tell me about your project..." required></textarea>
                    </div>
                    <button type="submit" class="form-submit">Send Message &rarr;</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        &copy; <?php echo date('Y'); ?> <span><?php echo e($profile['full_name']); ?></span> &mdash; Web Technologies Project
    </footer>

</body>
</html>
