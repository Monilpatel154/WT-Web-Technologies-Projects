const { useEffect, useMemo, useState } = React;

function App() {
    const page = window.__SKILLSWAP_PAGE__;
    if (!page) return null;

    switch (page.type) {
        case 'login': return <LoginPage data={page.data || {}} />;
        case 'register': return <RegisterPage data={page.data || {}} />;
        case 'setup_profile': return <SetupProfilePage data={page.data || {}} />;
        case 'explore': return <ExplorePage data={page.data || {}} />;
        case 'skill_detail': return <SkillDetailPage data={page.data || {}} />;
        case 'profile_view': return <ProfileViewPage data={page.data || {}} />;
        case 'profile_edit': return <ProfileEditPage data={page.data || {}} />;
        case 'skill_form': return <SkillFormPage data={page.data || {}} />;
        case 'swaps': return <SwapsPage data={page.data || {}} />;
        case 'chat': return <ChatPage data={page.data || {}} />;
        case 'sessions': return <SessionsPage data={page.data || {}} />;
        case 'notifications': return <NotificationsPage data={page.data || {}} />;
        case 'admin_dashboard': return <AdminDashboardPage data={page.data || {}} />;
        case 'admin_users': return <AdminTablePage data={page.data || {}} />;
        case 'admin_skills': return <AdminTablePage data={page.data || {}} />;
        case 'admin_reports': return <AdminReportsPage data={page.data || {}} />;
        case 'admin_categories': return <AdminCategoriesPage data={page.data || {}} />;
        case 'admin_analytics': return <AdminAnalyticsPage data={page.data || {}} />;
        case 'smart_match': return <SmartMatchPage data={page.data || {}} />;
        case 'home': return <HomePage data={page.data || {}} />;
        case 'wanted_add': return <WantedAddPage data={page.data || {}} />;
        case 'session_detail': return <SessionDetailPage data={page.data || {}} />;
        case 'report_submit': return <ReportSubmitPage data={page.data || {}} />;
        case 'review_submit': return <ReviewSubmitPage data={page.data || {}} />;
        default:
            return <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}><div className="card">Unsupported page.</div></div>;
    }
}

function rootUrl(path) {
    return path.startsWith('/') ? path : `/${path}`;
}

function ratingStars(value) {
    const rating = Math.max(0, Math.min(5, Math.round(Number(value) || 0)));
    return Array.from({ length: 5 }, (_, index) => (
        <span key={index} className={index < rating ? 'star-on' : 'star-off'}>{index < rating ? '★' : '☆'}</span>
    ));
}

function cleanCategoryName(skill) {
    return skill.categoryName || skill.category || 'Skill';
}

function modeBadgeClass(mode) {
    if (mode === 'in-person') return 'badge-inperson';
    return `badge-${mode}`;
}
function confirmAction(event, message) {
    if (!window.confirm(message)) {
        event.preventDefault();
        event.stopPropagation();
        return false;
    }
    return true;
}

function HomePage({ data }) {
    const categories = data.categories || [];
    const featured = data.featured || [];
    const stats = data.stats || {};

    return (
        <>
            <section className="hero">
                <div className="container">
                    <div className="hero-content fade-up">
                        <div className="hero-badge">Skill exchange for students</div>
                        <h1 className="hero-title">Teach what you know.<br />Learn what you need.</h1>
                        <p className="hero-subtitle">SkillSwap helps students exchange practical skills without money. Build a profile, find matches, and start learning.</p>
                        <div className="hero-actions">
                            {data.loggedIn ? (
                                <>
                                    <a href="/match/smart_match.php" className="btn btn-primary btn-lg">Find matches</a>
                                    <a href="/skills/add.php" className="btn btn-ghost btn-lg">Add a skill</a>
                                </>
                            ) : (
                                <>
                                    <a href="/auth/register.php" className="btn btn-primary btn-lg">Create account</a>
                                    <a href="/skills/explore.php" className="btn btn-ghost btn-lg">Explore skills</a>
                                </>
                            )}
                        </div>
                        <div className="hero-stats">
                            <StatItem value={`${stats.totalUsers || 0}+`} label="Students" />
                            <StatItem value={`${stats.totalSkills || 0}+`} label="Skills" />
                            <StatItem value={`${stats.totalSwaps || 0}+`} label="Swaps" />
                            <StatItem value={`${stats.totalCategories || 0}`} label="Categories" />
                        </div>
                    </div>
                </div>
            </section>

            <section className="section section-alt">
                <div className="container">
                    <div className="section-header">
                        <h2 className="section-title">Browse by category</h2>
                        <p className="section-subtitle">Find the right people for the skills you want to teach or learn.</p>
                    </div>
                    <div className="category-grid">
                        {categories.map(category => (
                            <a key={category.id} href={`/skills/explore.php?category=${category.id}`} className="category-card">
                                <div className="category-name">{category.name}</div>
                                <div className="category-count">{category.count} skill{category.count === 1 ? '' : 's'}</div>
                            </a>
                        ))}
                    </div>
                </div>
            </section>

            <section className="section">
                <div className="container">
                    <div className="flex-between mb-3">
                        <div>
                            <h2 className="section-title" style={{ textAlign: 'left', marginBottom: '0.3rem' }}>Latest skills</h2>
                            <p className="text-muted">Fresh listings from students on campus</p>
                        </div>
                        <a href="/skills/explore.php" className="btn btn-ghost">View all</a>
                    </div>
                    <div className="skills-grid">
                        {featured.map(skill => <SkillCard key={skill.id} skill={skill} />)}
                    </div>
                </div>
            </section>

            <section className="section section-alt">
                <div className="container">
                    <div className="section-header">
                        <h2 className="section-title">How it works</h2>
                        <p className="section-subtitle">A simple flow from profile to first exchange.</p>
                    </div>
                    <div className="grid-3">
                        <InfoCard title="Add your skills" text="List what you can teach and what you want to learn." />
                        <InfoCard title="Find a match" text="Browse the platform and connect with the right student." />
                        <InfoCard title="Start swapping" text="Agree on a session and begin exchanging knowledge." />
                    </div>
                </div>
            </section>

            {!data.loggedIn && (
                <section className="section">
                    <div className="container-sm" style={{ textAlign: 'center' }}>
                        <div className="card">
                            <h2 className="section-title" style={{ fontSize: '1.7rem', marginBottom: '0.75rem' }}>Ready to start swapping?</h2>
                            <p className="text-muted" style={{ marginBottom: '1.5rem' }}>Join students already exchanging skills on campus.</p>
                            <a href="/auth/register.php" className="btn btn-primary btn-lg">Join free</a>
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}

function StatItem({ value, label }) {
    return <div className="stat-item"><div className="stat-num">{value}</div><div className="stat-label">{label}</div></div>;
}

function InfoCard({ title, text }) {
    return <div className="card" style={{ textAlign: 'center' }}><h3 style={{ fontSize: '1.05rem', marginBottom: '0.5rem' }}>{title}</h3><p className="text-muted" style={{ fontSize: '0.9rem' }}>{text}</p></div>;
}

function SkillCard({ skill }) {
    return (
        <a href={`/skills/detail.php?id=${skill.id}`} className="skill-card" style={{ textDecoration: 'none', color: 'inherit' }}>
            <div className="skill-card-body">
                <div className="skill-category-tag">{cleanCategoryName(skill)}</div>
                <div className="skill-title">{skill.title}</div>
                <div className="skill-desc">{skill.description}</div>
                <div className="skill-meta">
                    <div className="skill-user">
                        <img src={skill.avatar} alt={skill.userName} loading="lazy" decoding="async" />
                        <span>{skill.userName}</span>
                        <span className="stars" style={{ fontSize: '0.78rem' }}>{ratingStars(skill.rating)}</span>
                    </div>
                </div>
            </div>
            <div className="skill-card-footer" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span className="credit-badge">{skill.creditValue} credit{skill.creditValue === 1 ? '' : 's'}</span>
                <span className={`badge ${modeBadgeClass(skill.mode)}`}>{skill.modeLabel}</span>
            </div>
        </a>
    );
}

function LoginPage({ data }) {
    const panelStyle = {
        minHeight: '100dvh',
        display: 'flex',
        alignItems: 'stretch',
        background: 'var(--bg)',
    };
    const leftStyle = {
        flex: '0 0 45%',
        background: 'linear-gradient(145deg, #3B0764 0%, #7C3AED 45%, #EC4899 100%)',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '3rem 3rem',
        position: 'relative',
        overflow: 'hidden',
    };
    const rightStyle = {
        flex: 1,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '2rem',
        background: 'var(--bg)',
    };
    const cardStyle = {
        width: '100%',
        maxWidth: '420px',
        background: 'rgba(14,18,32,0.9)',
        border: '1px solid rgba(255,255,255,0.08)',
        borderRadius: '20px',
        padding: '2.5rem',
        backdropFilter: 'blur(24px)',
        boxShadow: '0 24px 80px rgba(0,0,0,0.6)',
    };

    return (
        <div className="auth-split auth-split-login" style={panelStyle}>
            {/* Left decorative panel */}
            <div className="auth-split-panel auth-split-panel-hero" style={leftStyle}>
                {/* Orbs */}
                <div style={{ position:'absolute', top:'-80px', right:'-80px', width:'300px', height:'300px', borderRadius:'50%', background:'rgba(255,255,255,0.06)', pointerEvents:'none' }} />
                <div style={{ position:'absolute', bottom:'-60px', left:'-60px', width:'220px', height:'220px', borderRadius:'50%', background:'rgba(255,255,255,0.05)', pointerEvents:'none' }} />

                <div style={{ position:'relative', zIndex:1, textAlign:'center', color:'#fff' }}>
                    <a href="/index.php" style={{ textDecoration:'none', color:'#fff' }}>
                        <div style={{ fontSize:'2rem', fontWeight:'900', letterSpacing:'-0.04em', marginBottom:'0.5rem' }}>Skill<span style={{ opacity:0.75 }}>Swap</span></div>
                    </a>
                    <p style={{ fontSize:'1.1rem', opacity:0.85, lineHeight:1.6, maxWidth:'280px', margin:'1.5rem auto 2.5rem' }}>
                        Exchange skills, grow together — no money needed.
                    </p>

                    {/* Testimonial-style cards */}
                    {[['🎸','"Learned guitar in 3 sessions!"','Priya, 2nd year'],
                      ['💻','"Got React help for my project"','Arjun, CS final year']].map(([icon,quote,name],i) => (
                        <div key={i} style={{ background:'rgba(255,255,255,0.1)', backdropFilter:'blur(8px)', border:'1px solid rgba(255,255,255,0.15)', borderRadius:'14px', padding:'1rem 1.25rem', marginBottom:'0.75rem', textAlign:'left' }}>
                            <div style={{ fontSize:'1.4rem', marginBottom:'0.4rem' }}>{icon}</div>
                            <div style={{ fontSize:'0.88rem', opacity:0.9, marginBottom:'0.3rem' }}>{quote}</div>
                            <div style={{ fontSize:'0.75rem', opacity:0.6, fontWeight:600 }}>— {name}</div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Right form panel */}
            <div className="auth-split-panel auth-split-panel-form" style={rightStyle}>
                <div className="auth-card auth-card-authpage" style={cardStyle}>
                    <div style={{ marginBottom:'1.75rem' }}>
                        <div style={{ display:'inline-flex', alignItems:'center', gap:'0.4rem', background:'rgba(124,58,237,0.12)', border:'1px solid rgba(124,58,237,0.25)', borderRadius:'999px', padding:'0.3rem 0.85rem', fontSize:'0.75rem', fontWeight:'700', color:'#C4B5FD', letterSpacing:'0.05em', textTransform:'uppercase', marginBottom:'1rem' }}>✦ Welcome back</div>
                        <h1 style={{ fontSize:'1.75rem', fontWeight:'900', letterSpacing:'-0.04em', marginBottom:'0.4rem', color:'var(--text-heading)' }}>Sign in to SkillSwap</h1>
                        <p style={{ color:'var(--text-muted)', fontSize:'0.9rem' }}>Don't have an account? <a href="/auth/register.php" style={{ color:'#A78BFA', fontWeight:600 }}>Join free →</a></p>
                    </div>

                    {data.error ? <div className="alert alert-error" style={{ marginBottom:'1.25rem' }}>{data.error}</div> : null}

                    <form method="POST" action={data.action || ''}>
                        {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                        {data.redirect ? <input type="hidden" name="redirect" value={data.redirect} /> : null}

                        <div style={{ marginBottom:'1rem' }}>
                            <label style={{ display:'block', fontSize:'0.82rem', fontWeight:'700', color:'var(--text-muted)', marginBottom:'0.45rem', textTransform:'uppercase', letterSpacing:'0.05em' }} htmlFor="email">Email Address</label>
                            <input type="email" id="email" name="email" className="form-control" defaultValue={data.email || ''} placeholder="you@college.edu" required autoFocus
                                style={{ background:'rgba(255,255,255,0.04)', border:'1px solid rgba(255,255,255,0.1)', borderRadius:'12px', padding:'0.8rem 1rem', fontSize:'0.95rem', color:'var(--text-heading)', width:'100%', transition:'border-color 0.2s' }} />
                        </div>

                        <div style={{ marginBottom:'1.5rem' }}>
                            <label style={{ display:'block', fontSize:'0.82rem', fontWeight:'700', color:'var(--text-muted)', marginBottom:'0.45rem', textTransform:'uppercase', letterSpacing:'0.05em' }} htmlFor="password">Password</label>
                            <input type="password" id="password" name="password" className="form-control" placeholder="Your password" required
                                style={{ background:'rgba(255,255,255,0.04)', border:'1px solid rgba(255,255,255,0.1)', borderRadius:'12px', padding:'0.8rem 1rem', fontSize:'0.95rem', color:'var(--text-heading)', width:'100%' }} />
                        </div>

                        <button type="submit" className="btn btn-primary btn-block btn-lg" style={{ width:'100%', justifyContent:'center', borderRadius:'12px', padding:'0.9rem', fontSize:'1rem', fontWeight:'800' }}>Sign In →</button>
                    </form>
                </div>
            </div>
        </div>
    );
}

function RegisterPage({ data }) {
    const panelStyle = {
        minHeight: '100dvh',
        display: 'flex',
        alignItems: 'stretch',
        background: 'var(--bg)',
    };
    const leftStyle = {
        flex: '0 0 40%',
        background: 'linear-gradient(145deg, #1E1B4B 0%, #6D28D9 50%, #EC4899 100%)',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '3rem 2.5rem',
        position: 'relative',
        overflow: 'hidden',
    };
    const rightStyle = {
        flex: 1,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '2rem',
        overflowY: 'auto',
    };
    const cardStyle = {
        width: '100%',
        maxWidth: '500px',
        background: 'rgba(14,18,32,0.9)',
        border: '1px solid rgba(255,255,255,0.08)',
        borderRadius: '20px',
        padding: '2.5rem',
        backdropFilter: 'blur(24px)',
        boxShadow: '0 24px 80px rgba(0,0,0,0.6)',
        margin: '2rem 0',
    };
    const inputStyle = {
        background: 'rgba(255,255,255,0.04)',
        border: '1px solid rgba(255,255,255,0.1)',
        borderRadius: '12px',
        padding: '0.8rem 1rem',
        fontSize: '0.95rem',
        color: 'var(--text-heading)',
        width: '100%',
    };
    const labelStyle = {
        display: 'block',
        fontSize: '0.82rem',
        fontWeight: '700',
        color: 'var(--text-muted)',
        marginBottom: '0.45rem',
        textTransform: 'uppercase',
        letterSpacing: '0.05em',
    };

    const perks = [
        ['⚡', 'Instant skill matching'],
        ['🎓', 'Campus-aware connections'],
        ['💬', 'Built-in messaging & sessions'],
        ['🆓', '100% free — no payments ever'],
    ];

    return (
        <div className="auth-split auth-split-register" style={panelStyle}>
            {/* Left decorative panel */}
            <div className="auth-split-panel auth-split-panel-hero" style={leftStyle}>
                <div style={{ position:'absolute', top:'-100px', right:'-100px', width:'350px', height:'350px', borderRadius:'50%', background:'rgba(255,255,255,0.05)', pointerEvents:'none' }} />
                <div style={{ position:'absolute', bottom:'-80px', left:'-80px', width:'250px', height:'250px', borderRadius:'50%', background:'rgba(255,255,255,0.04)', pointerEvents:'none' }} />

                <div style={{ position:'relative', zIndex:1, textAlign:'center', color:'#fff' }}>
                    <a href="/index.php" style={{ textDecoration:'none', color:'#fff' }}>
                        <div style={{ fontSize:'2rem', fontWeight:'900', letterSpacing:'-0.04em', marginBottom:'0.5rem' }}>Skill<span style={{ opacity:0.75 }}>Swap</span></div>
                    </a>
                    <p style={{ fontSize:'1rem', opacity:0.8, lineHeight:1.65, maxWidth:'260px', margin:'1.25rem auto 2rem' }}>
                        Join thousands of students teaching and learning from each other.
                    </p>

                    {perks.map(([icon, text], i) => (
                        <div key={i} style={{ display:'flex', alignItems:'center', gap:'0.85rem', background:'rgba(255,255,255,0.08)', borderRadius:'12px', padding:'0.8rem 1.1rem', marginBottom:'0.6rem', textAlign:'left' }}>
                            <span style={{ fontSize:'1.3rem' }}>{icon}</span>
                            <span style={{ fontSize:'0.88rem', fontWeight:'600', opacity:0.9 }}>{text}</span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Right form panel */}
            <div className="auth-split-panel auth-split-panel-form" style={rightStyle}>
                <div className="auth-card auth-card-authpage" style={cardStyle}>
                    <div style={{ marginBottom:'1.75rem' }}>
                        <div style={{ display:'inline-flex', alignItems:'center', gap:'0.4rem', background:'rgba(124,58,237,0.12)', border:'1px solid rgba(124,58,237,0.25)', borderRadius:'999px', padding:'0.3rem 0.85rem', fontSize:'0.75rem', fontWeight:'700', color:'#C4B5FD', letterSpacing:'0.05em', textTransform:'uppercase', marginBottom:'1rem' }}>✦ Create account</div>
                        <h1 style={{ fontSize:'1.75rem', fontWeight:'900', letterSpacing:'-0.04em', marginBottom:'0.4rem', color:'var(--text-heading)' }}>Join SkillSwap</h1>
                        <p style={{ color:'var(--text-muted)', fontSize:'0.9rem' }}>Already have an account? <a href="/auth/login.php" style={{ color:'#A78BFA', fontWeight:600 }}>Sign in →</a></p>
                    </div>

                    {Array.isArray(data.errors) && data.errors.length > 0 ? (
                        <div className="alert alert-error" style={{ marginBottom:'1.25rem' }}>{data.errors.map((err, i) => <div key={i}>• {err}</div>)}</div>
                    ) : null}

                    <form method="POST" action={data.action || ''}>
                        {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}

                        <div style={{ marginBottom:'1rem' }}>
                            <label style={labelStyle} htmlFor="name">Full Name</label>
                            <input type="text" id="name" name="name" className="form-control" defaultValue={data.form?.name || ''} placeholder="Arjun Sharma" required autoFocus style={inputStyle} />
                        </div>

                        <div style={{ marginBottom:'1rem' }}>
                            <label style={labelStyle} htmlFor="email">Email Address</label>
                            <input type="email" id="email" name="email" className="form-control" defaultValue={data.form?.email || ''} placeholder="you@college.edu" required style={inputStyle} />
                        </div>

                        <div style={{ marginBottom:'1rem' }}>
                            <label style={labelStyle} htmlFor="college_id">College / University</label>
                            <select id="college_id" name="college_id" className="form-control" required defaultValue={data.form?.college_id || ''} style={{ ...inputStyle, appearance:'none' }}>
                                <option value="">— Select your institution —</option>
                                {(data.colleges || []).map(c => <option key={c.id} value={c.id}>{c.name}{c.city ? ` (${c.city})` : ''}</option>)}
                            </select>
                        </div>

                        <div className="auth-form-grid" style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:'1rem', marginBottom:'1.5rem' }}>
                            <div>
                                <label style={labelStyle} htmlFor="password">Password</label>
                                <input type="password" id="password" name="password" className="form-control" placeholder="Min. 6 chars" required style={inputStyle} />
                            </div>
                            <div>
                                <label style={labelStyle} htmlFor="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" className="form-control" placeholder="Repeat" required style={inputStyle} />
                            </div>
                        </div>

                        <button type="submit" className="btn btn-primary btn-block btn-lg" style={{ width:'100%', justifyContent:'center', borderRadius:'12px', padding:'0.9rem', fontSize:'1rem', fontWeight:'800' }}>Create Account →</button>

                        <p style={{ textAlign:'center', fontSize:'0.78rem', color:'var(--text-dim)', marginTop:'1.25rem', lineHeight:1.5 }}>
                            By signing up you agree to our <a href="#" style={{ color:'#A78BFA' }}>Terms</a> and <a href="#" style={{ color:'#A78BFA' }}>Privacy Policy</a>.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    );
}

function SetupProfilePage({ data }) {
    const days = [
        ['mon', 'Mon'], ['tue', 'Tue'], ['wed', 'Wed'], ['thu', 'Thu'], ['fri', 'Fri'], ['sat', 'Sat'], ['sun', 'Sun'],
    ];

    return (
        <div className="auth-container" style={{ alignItems: 'flex-start', paddingTop: '3rem' }}>
            <div className="auth-card auth-card-wide">
                <div className="auth-logo"><a href="/index.php" className="auth-brand">SkillSwap</a></div>
                <h1 className="auth-title">Welcome, {data.firstName || 'there'}.</h1>
                <p className="auth-sub">Set up your profile so others can find you</p>
                <form method="POST" action={data.action || ''}>
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <div className="form-group">
                        <label className="form-label" htmlFor="bio">Bio <span className="text-dim">(optional)</span></label>
                        <textarea id="bio" name="bio" className="form-control" rows="4" maxLength="500" placeholder="Tell others what you're passionate about..." defaultValue={data.bio || ''} />
                    </div>
                    <div className="form-group">
                        <label className="form-label">Weekly Availability</label>
                        <div className="availability-grid">
                            {days.map(([key, label]) => (
                                <div key={key} className="availability-day">
                                    <span className="day-label">{label}</span>
                                    <select name={`avail_${key}`} className="form-control" defaultValue="">
                                        <option value="">Off</option>
                                        <option value="morning">AM</option>
                                        <option value="afternoon">PM</option>
                                        <option value="evening">Eve</option>
                                        <option value="anytime">Any</option>
                                    </select>
                                </div>
                            ))}
                        </div>
                    </div>
                    <button type="submit" className="btn btn-primary btn-block btn-lg">Continue</button>
                </form>
                <p style={{ textAlign: 'center', marginTop: '1rem', fontSize: '0.85rem', color: 'var(--text-muted)' }}><a href="/index.php">Skip for now</a></p>
            </div>
        </div>
    );
}

function ExplorePage({ data }) {
    const skills = data.skills || [];
    const categories = data.categories || [];
    const maxDistance = data.maxDistance || 50;

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="flex-between mb-3">
                <div>
                    <h1 className="page-title">Explore Skills</h1>
                    <p className="text-muted">{data.count || skills.length} skill{(data.count || skills.length) === 1 ? '' : 's'} available</p>
                </div>
                {data.loggedIn ? <a href="/skills/add.php" className="btn btn-primary">Add your skill</a> : null}
            </div>

            <form method="GET" className="filter-bar">
                <div className="filter-group" style={{ flex: 2 }}>
                    <label className="filter-label">Search</label>
                    <input type="text" name="search" className="form-control" placeholder="React, Guitar, Figma..." defaultValue={data.search || ''} />
                </div>
                <div className="filter-group">
                    <label className="filter-label">Category</label>
                    <select name="category" className="form-control" defaultValue={data.category || ''}>
                        <option value="">All Categories</option>
                        {categories.map(category => <option key={category.id} value={category.id}>{category.name}</option>)}
                    </select>
                </div>
                <div className="filter-group">
                    <label className="filter-label">Mode</label>
                    <select name="mode" className="form-control" defaultValue={data.mode || ''}>
                        <option value="">Any Mode</option>
                        <option value="online">Online</option>
                        <option value="in-person">In-Person</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div className="filter-group">
                    <label className="filter-label">Sort</label>
                    <select name="sort" className="form-control" defaultValue={data.sort || 'newest'}>
                        <option value="newest">Newest First</option>
                        <option value="rating">Top Rated</option>
                        <option value="credits">Lowest Credits</option>
                        {data.canSortByDistance ? <option value="distance">Nearest First</option> : null}
                    </select>
                </div>
                {data.canSortByDistance ? (
                    <div className="filter-group">
                        <label className="filter-label">Distance: <span>{maxDistance} km</span></label>
                        <input type="range" name="distance" min="1" max="100" defaultValue={maxDistance} className="form-control" style={{ padding: '0.4rem 0' }} />
                    </div>
                ) : null}
                <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'flex-end' }}>
                    <button type="submit" className="btn btn-primary">Filter</button>
                    <a href="/skills/explore.php" className="btn btn-ghost">Reset</a>
                </div>
            </form>

            {skills.length === 0 ? (
                <div className="empty-state">
                    <div className="empty-icon">Search</div>
                    <h3>No skills found</h3>
                    <p>Try adjusting your filters or add the first skill in this category.</p>
                </div>
            ) : (
                <div className="skills-grid">
                    {skills.map(skill => <SkillCard key={skill.id} skill={skill} />)}
                </div>
            )}
        </div>
    );
}

function SkillDetailPage({ data }) {
    const skill = data.skill || {};
    const mySkills = data.mySkills || [];
    const reviews = data.reviews || [];
    const otherSkills = data.otherSkills || [];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="grid-2" style={{ gap: '2rem', alignItems: 'start' }}>
                <div>
                    <div className="card">
                        <div style={{ marginBottom: '1rem' }}>
                            <span className="skill-category-tag">{skill.categoryName}</span>
                            <span className={`badge ${modeBadgeClass(skill.mode)}`} style={{ marginLeft: '0.5rem' }}>{skill.modeLabel}</span>
                            {skill.distanceBadge ? <span className="badge badge-nearby" style={{ marginLeft: '0.5rem' }}>{skill.distanceBadge}</span> : null}
                        </div>
                        <h1 style={{ fontSize: '1.6rem', fontWeight: 800, marginBottom: '1rem' }}>{skill.title}</h1>
                        <p style={{ color: 'var(--text-muted)', lineHeight: 1.7, marginBottom: '1.5rem' }}>{skill.description}</p>
                        <div className="flex-center gap-2" style={{ paddingTop: '1rem', borderTop: '1px solid var(--border)' }}>
                            <div className="credit-badge" style={{ fontSize: '0.9rem', padding: '0.4rem 1rem' }}>{skill.creditValue} credit{skill.creditValue === 1 ? '' : 's'} per session</div>
                        </div>
                    </div>

                    {reviews.length > 0 ? (
                        <div className="card mt-3">
                            <div className="card-header">
                                <h3 className="card-title">Reviews for {skill.userName}</h3>
                                <span className="text-muted" style={{ fontSize: '0.85rem' }}>{skill.avgRating}/5 · {skill.reviewCount || reviews.length} reviews</span>
                            </div>
                            {reviews.map(review => (
                                <div key={review.id} style={{ padding: '0.85rem 0', borderBottom: '1px solid var(--border)' }}>
                                    <div className="flex-center gap-2 mb-1">
                                        <img src={review.reviewerAvatar} alt={review.reviewerName} style={{ width: 32, height: 32, borderRadius: '50%' }} />
                                        <span style={{ fontWeight: 600, fontSize: '0.88rem' }}>{review.reviewerName}</span>
                                        <span className="stars" style={{ fontSize: '0.8rem' }}>{ratingStars(review.rating)}</span>
                                        <span className="text-dim" style={{ fontSize: '0.78rem', marginLeft: 'auto' }}>{review.timeAgo}</span>
                                    </div>
                                    {review.comment ? <p style={{ fontSize: '0.88rem', color: 'var(--text-muted)', paddingLeft: '2.25rem' }}>{review.comment}</p> : null}
                                </div>
                            ))}
                        </div>
                    ) : null}
                </div>

                <div>
                    <div className="card mb-3">
                        <div className="flex-center gap-2 mb-3">
                            <img src={skill.ownerAvatar} alt={skill.userName} style={{ width: 56, height: 56, borderRadius: '50%', border: '2px solid rgba(37,99,235,0.18)' }} />
                            <div>
                                <div style={{ fontWeight: 700 }}>{skill.userName}</div>
                                <div style={{ fontSize: '0.82rem', color: 'var(--text-muted)' }}>{skill.college || ''}</div>
                                <div className="stars" style={{ fontSize: '0.8rem' }}>{ratingStars(skill.avgRating)} <span style={{ color: 'var(--text-dim)', fontSize: '0.75rem' }}> {skill.avgRating}/5</span></div>
                            </div>
                        </div>
                        {skill.bio ? <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '1rem' }}>{skill.bio}</p> : null}
                        {skill.distanceBadge ? <div style={{ marginBottom: '1rem' }}><span className="badge badge-nearby">{skill.distanceBadge}</span></div> : null}
                        <a href={`/profile/view.php?id=${skill.ownerId}`} className="btn btn-ghost btn-block">View full profile</a>
                    </div>

                    {data.isOwner ? (
                        <div className="card mb-3">
                            <h3 style={{ fontSize: '1rem', fontWeight: 700 }}>This is your skill</h3>
                            <div className="flex gap-2 mt-2">
                                <a href={`/skills/edit.php?id=${skill.id}`} className="btn btn-secondary btn-sm">Edit</a>
                                <a href={`/skills/delete.php?id=${skill.id}`} className="btn btn-danger btn-sm" onClick={(event) => confirmAction(event, 'Delete this skill listing?')}>Delete</a>
                            </div>
                        </div>
                    ) : null}

                    {data.loggedIn && !data.isOwner ? (
                        <div className="card mb-3">
                            <h3 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: '1rem' }}>Request a skill swap</h3>
                            {mySkills.length > 0 ? (
                                <form method="POST" action="/swaps/request.php">
                                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                                    <input type="hidden" name="receiver_skill_id" value={skill.id} />
                                    <input type="hidden" name="receiver_id" value={skill.ownerId} />
                                    <div className="form-group">
                                        <label className="form-label">I'll teach them</label>
                                        <select name="requester_skill_id" className="form-control" required defaultValue="">
                                            <option value="">Select your skill</option>
                                            {mySkills.map(item => <option key={item.id} value={item.id}>{item.title}</option>)}
                                        </select>
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Message (optional)</label>
                                        <textarea name="message" className="form-control" rows="3" placeholder="Hi! I'd love to swap skills with you..." />
                                    </div>
                                    <button type="submit" className="btn btn-primary btn-block">Send request</button>
                                </form>
                            ) : (
                                <>
                                    <p className="text-muted" style={{ fontSize: '0.88rem', marginBottom: '1rem' }}>Add a skill first so you can request a swap.</p>
                                    <a href="/skills/add.php" className="btn btn-primary btn-block">Add a skill first</a>
                                </>
                            )}
                        </div>
                    ) : null}

                    {!data.loggedIn ? (
                        <div className="card mb-3" style={{ textAlign: 'center' }}>
                            <p className="text-muted mb-2">Sign in to request a swap with {skill.userName}</p>
                            <a href="/auth/login.php" className="btn btn-primary btn-block">Sign in to swap</a>
                        </div>
                    ) : null}

                    {otherSkills.length > 0 ? (
                        <div className="card">
                            <h3 style={{ fontSize: '0.95rem', fontWeight: 700, marginBottom: '1rem' }}>Other skills by {skill.userFirstName || skill.userName.split(' ')[0]}</h3>
                            {otherSkills.map(item => (
                                <a key={item.id} href={`/skills/detail.php?id=${item.id}`} style={{ display: 'block', padding: '0.65rem 0', borderBottom: '1px solid var(--border)', textDecoration: 'none', color: 'inherit' }}>
                                    <div style={{ fontSize: '0.82rem', color: 'var(--text-muted)' }}>{item.categoryName}</div>
                                    <div style={{ fontWeight: 600, fontSize: '0.9rem' }}>{item.title}</div>
                                </a>
                            ))}
                        </div>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

function ProfileViewPage({ data }) {
    const user = data.user || {};
    const skills = data.skills || [];
    const wants = data.wants || [];
    const reviews = data.reviews || [];
    const isSelf = Boolean(data.viewerIsSelf);

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="page-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', flexWrap: 'wrap' }}>
                <div>
                    <h1 className="page-title" style={{ marginBottom: '0.35rem' }}>{isSelf ? 'My Profile' : `${user.name}'s Profile`}</h1>
                    <p className="page-subtitle">{isSelf ? 'This is your public profile and account overview.' : 'Public profile, skills, wants, and reviews.'}</p>
                </div>
                {isSelf ? (
                    <div style={{ display: 'flex', gap: '0.75rem', flexWrap: 'wrap' }}>
                        <a href="/profile/edit.php" className="btn btn-primary">Edit profile</a>
                        <a href="/skills/add.php" className="btn btn-secondary">Add skill</a>
                        <a href="/wanted/add.php" className="btn btn-ghost">Add want</a>
                    </div>
                ) : (
                    <a href={`/swaps/request.php?receiver_id=${user.id}`} className="btn btn-primary">Request swap</a>
                )}
            </div>

            <div className="grid-2" style={{ gap: '1rem' }}>
                <div className="card" style={{ padding: '1.5rem' }}>
                    <div className="profile-header">
                        <img src={user.avatar} alt={user.name} className="profile-avatar" />
                        <div>
                            <h2 className="profile-name">{user.name}</h2>
                            <div className="profile-meta">
                                <span className="stars">{ratingStars(user.avgRating)}</span>
                                <span className="meta-pill">{user.totalReviews || 0} reviews</span>
                                {user.collegeName ? <span className="meta-pill">{user.collegeName}</span> : null}
                                {data.distanceBadge ? <span className="meta-pill">{data.distanceBadge}</span> : null}
                            </div>
                        </div>
                    </div>
                    <p className="profile-bio">{user.bio || 'No bio available yet.'}</p>
                    <div className="profile-actions">
                        {data.viewerId && data.viewerId !== user.id ? <a href={`/reports/submit.php?reported_id=${user.id}`} className="btn btn-ghost">Report user</a> : null}
                        {isSelf ? <a href="/profile/edit.php" className="btn btn-secondary">Edit profile</a> : null}
                    </div>
                    {user.availability && user.availability.length > 0 ? (
                        <div className="profile-card mt-3">
                            <h4>Availability</h4>
                            <div className="availability-list">
                                {user.availability.map(day => <span key={day} className="badge badge-both">{day}</span>)}
                            </div>
                        </div>
                    ) : null}
                </div>

                <div>
                    <div className="card mb-3">
                        <h3>Offerings</h3>
                        {skills.length === 0 ? <p className="muted-text">No active skills offered yet.</p> : skills.map(skill => (
                            <div key={skill.id} className="profile-skill-card">
                                <div>
                                    <div className="skill-category">{skill.categoryName}</div>
                                    <h4>{skill.title}</h4>
                                    <p>{skill.description}</p>
                                </div>
                                <span className="badge badge-secondary">{skill.modeLabel}</span>
                            </div>
                        ))}
                    </div>
                    <div className="card mb-3">
                        <h3>Wants</h3>
                        {wants.length === 0 ? <p className="muted-text">No skill wants listed yet.</p> : (
                            <ul className="list-simple">
                                {wants.map(want => <li key={want.id}><strong>{want.categoryName}</strong> - {want.description}</li>)}
                            </ul>
                        )}
                    </div>
                    <div className="card">
                        <h3>Recent Reviews</h3>
                        {reviews.length === 0 ? <p className="muted-text">No reviews yet.</p> : reviews.map(review => (
                            <div key={review.id} className="review-item">
                                <div className="review-header">
                                    <strong>{review.reviewerName}</strong>
                                    <span className="stars">{ratingStars(review.rating)}</span>
                                </div>
                                <p>{review.comment || 'No comment provided.'}</p>
                                <div className="review-time">{review.timeAgo}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

function ProfileEditPage({ data }) {
    const availability = data.availability || [];
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <h2 className="section-title">Edit profile</h2>
            {Array.isArray(data.errors) && data.errors.length > 0 ? <div className="alert alert-error">{data.errors.map((err, index) => <div key={index}>• {err}</div>)}</div> : null}
            <div className="card">
                <form method="POST" action="/profile/edit.php">
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <div className="form-group">
                        <label className="form-label" htmlFor="name">Full Name</label>
                        <input id="name" name="name" type="text" className="form-control" defaultValue={data.user?.name || ''} required />
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="bio">Bio</label>
                        <textarea id="bio" name="bio" className="form-control" rows="5" maxLength="500" defaultValue={data.user?.bio || ''} />
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="college_id">College</label>
                        <select id="college_id" name="college_id" className="form-control" defaultValue={data.user?.collegeId || 0}>
                            <option value="0">Select your college</option>
                            {(data.colleges || []).map(college => <option key={college.id} value={college.id}>{college.name}</option>)}
                        </select>
                    </div>
                    <div className="form-group">
                        <label className="form-label">Weekly Availability</label>
                        <div className="availability-grid">
                            {days.map(day => (
                                <label key={day} className="availability-chip">
                                    <input type="checkbox" name={`availability[${day}]`} value="1" defaultChecked={availability.includes(day)} />
                                    {day.charAt(0).toUpperCase() + day.slice(1)}
                                </label>
                            ))}
                        </div>
                        <p className="hint-text">Mark the days when you are available for skill sessions.</p>
                    </div>
                    <div className="flex gap-2">
                        <button type="submit" className="btn btn-primary">Save profile</button>
                        <a href={`/profile/view.php?id=${data.user?.id || ''}`} className="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    );
}

function SkillFormPage({ data }) {
    const skill = data.skill || {};
    const title = data.mode === 'edit' ? 'Edit skill' : 'Add a skill';

    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="page-header">
                <h1 className="page-title">{title}</h1>
                <p className="page-subtitle">{data.mode === 'edit' ? 'Update your listing details' : 'List a skill you can teach'}</p>
            </div>
            {Array.isArray(data.errors) && data.errors.length > 0 ? <div className="alert alert-error">{data.errors.map((err, index) => <div key={index}>• {err}</div>)}</div> : null}
            <div className="card">
                <form method="POST" action={data.action || ''}>
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <div className="form-group">
                        <label className="form-label" htmlFor="title">Skill title *</label>
                        <input id="title" name="title" type="text" className="form-control" required defaultValue={skill.title || ''} placeholder="React.js Development, Guitar for Beginners, Figma UI/UX" />
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="category_id">Category *</label>
                        <select id="category_id" name="category_id" className="form-control" required defaultValue={skill.categoryId || ''}>
                            <option value="">Select category</option>
                            {(data.categories || []).map(category => <option key={category.id} value={category.id}>{category.name}</option>)}
                        </select>
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="description">Description</label>
                        <textarea id="description" name="description" className="form-control" rows="4" maxLength="1000" defaultValue={skill.description || ''} placeholder="What will you teach? What's the format?" />
                    </div>
                    <div className="form-row">
                        <div className="form-group">
                            <label className="form-label" htmlFor="credit_value">Credit value</label>
                            <select id="credit_value" name="credit_value" className="form-control" defaultValue={skill.creditValue || 1}>
                                {[1, 2, 3, 4, 5].map(value => <option key={value} value={value}>{value} credit{value === 1 ? '' : 's'} per session</option>)}
                            </select>
                        </div>
                        <div className="form-group">
                            <label className="form-label" htmlFor="mode">Session mode</label>
                            <select id="mode" name="mode" className="form-control" defaultValue={skill.mode || 'both'}>
                                <option value="both">Online & In-Person</option>
                                <option value="online">Online only</option>
                                <option value="in-person">In-Person only</option>
                            </select>
                        </div>
                    </div>
                    {data.mode === 'edit' ? (
                        <div className="form-group">
                            <label className="form-label" htmlFor="status">Status</label>
                            <select id="status" name="status" className="form-control" defaultValue={skill.status || 'active'}>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    ) : null}
                    <div style={{ display: 'flex', gap: '1rem', marginTop: '0.5rem' }}>
                        <button type="submit" className="btn btn-primary btn-lg">{data.mode === 'edit' ? 'Save changes' : 'Add skill'}</button>
                        <a href={data.cancelUrl || '/skills/explore.php'} className="btn btn-ghost btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    );
}

function SwapsPage({ data }) {
    const tab = data.tab || 'incoming';
    const incoming = data.incoming || [];
    const outgoing = data.outgoing || [];
    const active = data.active || [];

    const tabs = [
        { key: 'incoming', label: 'Incoming', count: incoming.length },
        { key: 'outgoing', label: 'Sent', count: outgoing.length },
        { key: 'active', label: 'Active / Done', count: active.length },
    ];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="page-header">
                <h1 className="page-title">My swaps</h1>
                <p className="page-subtitle">Manage incoming, outgoing, and completed requests</p>
            </div>

            <div style={{ display: 'flex', gap: '0.5rem', marginBottom: '1.5rem', borderBottom: '1px solid var(--border)', paddingBottom: '0' }}>
                {tabs.map(item => (
                    <a key={item.key} href={`?tab=${item.key}`} style={{ padding: '0.65rem 1.25rem', borderBottom: `2px solid ${tab === item.key ? 'var(--primary)' : 'transparent'}`, color: tab === item.key ? 'var(--primary)' : 'var(--text-muted)', fontWeight: 600, fontSize: '0.9rem', textDecoration: 'none' }}>
                        {item.label} {item.count > 0 ? <span style={{ background: 'rgba(37,99,235,0.1)', color: 'var(--primary)', fontSize: '0.75rem', padding: '0.15rem 0.5rem', borderRadius: '20px', marginLeft: '0.35rem' }}>{item.count}</span> : null}
                    </a>
                ))}
            </div>

            {tab === 'incoming' ? <SwapList type="incoming" items={incoming} data={data} /> : null}
            {tab === 'outgoing' ? <SwapList type="outgoing" items={outgoing} data={data} /> : null}
            {tab === 'active' ? <SwapList type="active" items={active} data={data} /> : null}
        </div>
    );
}

function SwapList({ type, items, data }) {
    if (items.length === 0) {
        const copy = {
            incoming: ['No incoming requests', 'When someone sends you a request, it will appear here.', '/skills/explore.php', 'Explore skills'],
            outgoing: ['No outgoing requests', 'Browse skills and send a swap request to start.', '/skills/explore.php', 'Explore skills'],
            active: ['No active swaps yet', 'Accept or send swap requests to get started.', null, null],
        }[type];
        return (
            <div className="empty-state">
                <div className="empty-icon">{copy[0]}</div>
                <h3>{copy[0]}</h3>
                <p>{copy[1]}</p>
                {copy[2] ? <a href={copy[2]} className="btn btn-primary mt-2">{copy[3]}</a> : null}
            </div>
        );
    }

    if (type === 'incoming') {
        return items.map(item => (
            <div key={item.id} className="swap-card">
                <img src={item.avatar} className="swap-avatar" alt={item.name} />
                <div className="swap-info">
                    <div className="swap-title">{item.name} wants to swap</div>
                    <div className="swap-skills"><span>{item.theirSkill}</span><span className="arrow">⇄</span><span>{item.mySkill}</span></div>
                    {item.message ? <div style={{ fontSize: '0.82rem', color: 'var(--text-dim)', fontStyle: 'italic', marginTop: '0.25rem' }}>“{item.message}”</div> : null}
                    <div className="swap-meta mt-1"><span className="text-dim" style={{ fontSize: '0.78rem' }}>{item.timeAgo}</span>{item.college ? <span className="badge badge-nearby">{item.college}</span> : null}</div>
                </div>
                <div className="swap-actions">
                    <form method="POST" action="/swaps/respond.php" style={{ display: 'inline' }}>
                        {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                        <input type="hidden" name="swap_id" value={item.id} />
                        <input type="hidden" name="action" value="accept" />
                        <button className="btn btn-success btn-sm">Accept</button>
                    </form>
                    <form method="POST" action="/swaps/respond.php" style={{ display: 'inline' }}>
                        {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                        <input type="hidden" name="swap_id" value={item.id} />
                        <input type="hidden" name="action" value="decline" />
                        <button type="submit" className="btn btn-danger btn-sm" onClick={(event) => confirmAction(event, 'Decline this swap request?')}>Decline</button>
                    </form>
                </div>
            </div>
        ));
    }

    if (type === 'outgoing') {
        return items.map(item => (
            <div key={item.id} className="swap-card">
                <img src={item.avatar} className="swap-avatar" alt={item.name} />
                <div className="swap-info">
                    <div className="swap-title">Request to {item.name}</div>
                    <div className="swap-skills"><span>{item.mySkill}</span><span className="arrow">⇄</span><span>{item.theirSkill}</span></div>
                    <div className="swap-meta mt-1"><span className={`badge badge-${item.status}`}>{item.statusLabel}</span><span className="text-dim" style={{ fontSize: '0.78rem' }}>{item.timeAgo}</span></div>
                </div>
                <div className="swap-actions">
                    {item.status === 'accepted' ? <a href={`/messages/chat.php?swap_id=${item.id}`} className="btn btn-primary btn-sm">Chat</a> : null}
                    {item.status === 'pending' ? (
                        <form method="POST" action="/swaps/respond.php">
                            {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                            <input type="hidden" name="swap_id" value={item.id} />
                            <input type="hidden" name="action" value="cancel" />
                            <button type="submit" className="btn btn-danger btn-sm" onClick={(event) => confirmAction(event, 'Cancel this request?')}>Cancel</button>
                        </form>
                    ) : null}
                </div>
            </div>
        ));
    }

    return items.map(item => (
        <div key={item.id} className="swap-card">
            <img src={item.avatar} className="swap-avatar" alt={item.name} />
            <div className="swap-info">
                <div className="swap-title">Swap with {item.name}</div>
                <div className="swap-skills"><span>{item.mySkill}</span><span className="arrow">⇄</span><span>{item.theirSkill}</span></div>
                <div className="swap-meta mt-1"><span className={`badge badge-${item.status}`}>{item.statusLabel}</span></div>
            </div>
            <div className="swap-actions">
                {item.status === 'accepted' ? (
                    <>
                        <a href={`/messages/chat.php?swap_id=${item.id}`} className="btn btn-primary btn-sm">Chat</a>
                        <a href={`/sessions/detail.php?swap_id=${item.id}`} className="btn btn-ghost btn-sm">Session</a>
                    </>
                ) : (
                    <a href={`/reviews/submit.php?swap_id=${item.id}&reviewee=${item.otherId}`} className="btn btn-secondary btn-sm">Review</a>
                )}
            </div>
        </div>
    ));
}

function ChatPage({ data }) {
    const initialMessages = data.messages || [];
    const [messages, setMessages] = useState(initialMessages);
    const [message, setMessage] = useState('');
    const [sending, setSending] = useState(false);

    const lastId = messages.length > 0 ? messages[messages.length - 1].id : 0;

    useEffect(() => {
        let mounted = true;
        const poll = async () => {
            try {
                const res = await fetch(`/messages/fetch.php?swap_id=${data.swapId}&after=${lastId}`);
                const json = await res.json();
                if (mounted && Array.isArray(json.messages) && json.messages.length > 0) {
                    setMessages(prev => [...prev, ...json.messages]);
                }
            } catch (error) {
                // ignore
            }
        };
        const timer = setInterval(poll, 3000);
        return () => { mounted = false; clearInterval(timer); };
    }, [data.swapId, lastId]);

    useEffect(() => {
        const chatBox = document.getElementById('chatMessages');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }, [messages]);

    const handleSubmit = async event => {
        event.preventDefault();
        const trimmed = message.trim();
        if (!trimmed) return;
        setSending(true);
        try {
            const formData = new FormData();
            formData.append('message', trimmed);
            if (data.csrfToken) formData.append('csrf_token', data.csrfToken);
            formData.append('ajax', '1');
            const res = await fetch(`/messages/chat.php?swap_id=${data.swapId}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await res.json();
            if (json.status === 'ok' && json.message) {
                setMessages(prev => [...prev, { ...json.message, id: json.message.message_id }]);
                setMessage('');
            }
        } finally {
            setSending(false);
        }
    };

    return (
        <div className="container-md" style={{ paddingTop: '1.5rem', paddingBottom: '2rem' }}>
            <div className="card mb-2" style={{ padding: '1rem 1.25rem' }}>
                <div className="flex-between">
                    <div className="flex-center gap-2">
                        <img src={data.other.avatar} alt={data.other.name} style={{ width: 40, height: 40, borderRadius: '50%' }} />
                        <div>
                            <div style={{ fontWeight: 700 }}>{data.other.name}</div>
                            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{data.swap.requesterSkill} ⇄ {data.swap.receiverSkill}</div>
                        </div>
                    </div>
                    <div className="flex gap-1">
                        <a href={`/sessions/detail.php?swap_id=${data.swapId}`} className="btn btn-ghost btn-sm">Session</a>
                        <a href={`/profile/view.php?id=${data.other.id}`} className="btn btn-ghost btn-sm">Profile</a>
                    </div>
                </div>
            </div>

            {data.meetupLink ? (
                <div className="meetup-banner mb-2">
                    <span style={{ fontSize: '0.88rem' }}><strong>Suggested meet spot</strong> - halfway between both of you ({data.distanceKm} km apart)</span>
                    <a href={data.meetupLink} target="_blank" rel="noopener noreferrer" className="btn btn-secondary btn-sm">Google Maps</a>
                </div>
            ) : null}

            <div className="chat-container">
                <div className="chat-messages" id="chatMessages" data-user-id={data.me.id}>
                    {messages.length === 0 ? <div style={{ textAlign: 'center', color: 'var(--text-dim)', padding: '2rem', fontSize: '0.88rem' }}>Start the conversation and coordinate your skill swap.</div> : null}
                    {messages.map(msg => (
                        <div key={msg.id} className={`message-item${msg.senderId === data.me.id ? ' sent' : ''}`}>
                            <img src={msg.avatar} alt={msg.name} className="message-avatar" />
                            <div>
                                <div className="message-bubble">{msg.message}</div>
                                <div className="message-time">{msg.timeAgo}</div>
                            </div>
                        </div>
                    ))}
                </div>

                <form className="chat-input-zone" onSubmit={handleSubmit}>
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <input type="text" id="chatInput" className="chat-input" placeholder="Type a message..." value={message} onChange={e => setMessage(e.target.value)} />
                    <button type="submit" className="btn btn-primary" disabled={sending}>{sending ? 'Sending...' : 'Send'}</button>
                </form>
            </div>
        </div>
    );
}

function SessionsPage({ data }) {
    const sessions = data.sessions || [];
    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="page-header">
                <h1 className="page-title">My sessions</h1>
                <p className="page-subtitle">Upcoming and past skill exchange sessions</p>
            </div>
            {sessions.length === 0 ? (
                <div className="empty-state">
                    <div className="empty-icon">Calendar</div>
                    <h3>No sessions yet</h3>
                    <p>Accept a swap request and schedule your first session.</p>
                    <a href="/swaps/my_requests.php" className="btn btn-primary mt-2">View swaps</a>
                </div>
            ) : sessions.map(session => (
                <div key={session.id} className="card mb-2">
                    <div className="flex-between flex-wrap gap-2">
                        <div>
                            <div style={{ fontWeight: 700, fontSize: '1rem' }}>{session.icon} Session with {session.otherName}</div>
                            <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginTop: '0.25rem' }}>{session.mySkill} ⇄ {session.theirSkill}</div>
                            {session.scheduledAt ? <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginTop: '0.25rem' }}>{session.scheduledAt}</div> : null}
                            {session.meetLink ? <div style={{ marginTop: '0.5rem' }}><a href={session.meetLink} target="_blank" rel="noopener noreferrer" className="btn btn-secondary btn-sm">Join meeting</a></div> : null}
                        </div>
                        <div className="flex gap-1 flex-wrap">
                            <a href={`/sessions/detail.php?swap_id=${session.swapId}`} className="btn btn-ghost btn-sm">Details</a>
                            <a href={`/messages/chat.php?swap_id=${session.swapId}`} className="btn btn-primary btn-sm">Chat</a>
                            {session.canReview ? <a href={`/reviews/submit.php?swap_id=${session.swapId}&reviewee=${session.otherId}`} className="btn btn-secondary btn-sm">Review</a> : null}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}

function NotificationsPage({ data }) {
    const notifications = data.notifications || [];
    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header mb-3" style={{ textAlign: 'left' }}>
                <h2 className="section-title" style={{ margin: 0 }}>Notifications</h2>
                {notifications.length > 0 ? <a href="/notifications/mark_read.php" className="btn btn-ghost btn-sm">Mark all read</a> : null}
            </div>

            {notifications.length === 0 ? (
                <div className="empty-state">
                    <div className="empty-icon">Bell</div>
                    <h3>All caught up</h3>
                    <p>No notifications yet. Once you start swapping skills, updates will appear here.</p>
                    <a href="/skills/explore.php" className="btn btn-primary">Browse skills</a>
                </div>
            ) : (
                <div className="card" style={{ padding: 0, overflow: 'hidden' }}>
                    {notifications.map((item, index) => (
                        <React.Fragment key={item.id}>
                            <a href={item.link} className={`notif-row ${item.isRead ? 'read' : 'unread'}`}>
                                <span className="notif-icon">{item.icon}</span>
                                <div className="notif-body">
                                    <div>{item.message}</div>
                                    <div className="notif-time">{item.timeAgo}</div>
                                </div>
                                {!item.isRead ? <span className="notif-dot" /> : null}
                            </a>
                            {index < notifications.length - 1 ? <hr style={{ margin: 0, borderColor: 'var(--border)' }} /> : null}
                        </React.Fragment>
                    ))}
                </div>
            )}
        </div>
    );
}

function AdminDashboardPage({ data }) {
    const stats = data.stats || {};
    const topCategories = data.topCategories || [];
    const recentReports = data.recentReports || [];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header">
                <div>
                    <h2 className="section-title">Admin dashboard</h2>
                    <p className="section-subtitle" style={{ textAlign: 'left' }}>Monitor platform activity, review reports, and manage users.</p>
                </div>
                <div className="flex gap-2">
                    <a href="/admin/users.php" className="btn btn-secondary btn-sm">Users</a>
                    <a href="/admin/reports.php" className="btn btn-secondary btn-sm">Reports</a>
                    <a href="/admin/skills.php" className="btn btn-secondary btn-sm">Skills</a>
                </div>
            </div>

            <div className="dashboard-grid">
                <div className="stat-card"><h3>{stats.users || 0}</h3><p>Registered users</p></div>
                <div className="stat-card"><h3>{stats.skills || 0}</h3><p>Active skills</p></div>
                <div className="stat-card"><h3>{stats.pendingSwaps || 0}</h3><p>Pending swap requests</p></div>
                <div className="stat-card"><h3>{stats.scheduledSessions || 0}</h3><p>Scheduled sessions</p></div>
                <div className="stat-card"><h3>{stats.pendingReports || 0}</h3><p>Pending reports</p></div>
                <div className="stat-card"><h3>{stats.notifications || 0}</h3><p>Total notifications</p></div>
            </div>

            <div className="card" style={{ marginTop: '1.5rem' }}>
                <h3>Top skill categories</h3>
                <div className="category-list">
                    {topCategories.map(category => <div key={category.name} className="category-pill">{category.name} <span>{category.total}</span></div>)}
                </div>
            </div>

            <div className="card" style={{ marginTop: '1.5rem' }}>
                <h3>Recent reports</h3>
                {recentReports.length === 0 ? <p className="muted-text">No reports waiting right now.</p> : (
                    <ul className="list-simple">
                        {recentReports.map(report => <li key={report.id}><strong>{report.reporterName}</strong> reported <strong>{report.reportedName}</strong> for <em>{report.reason}</em> · {report.timeAgo}</li>)}
                    </ul>
                )}
            </div>
        </div>
    );
}

function AdminTablePage({ data }) {
    const columns = data.columns || [];
    const rows = data.rows || [];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header">
                <div>
                    <h2 className="section-title">{data.title}</h2>
                    <p className="section-subtitle" style={{ textAlign: 'left' }}>{data.subtitle}</p>
                </div>
                {data.topAction ? <a href={data.topAction.href} className="btn btn-secondary btn-sm">{data.topAction.label}</a> : null}
            </div>

            <div className="card" style={{ padding: 0, overflow: 'hidden' }}>
                <div className="table-wrapper">
                    <table className="admin-table">
                        <thead>
                            <tr>
                                {columns.map(column => <th key={column}>{column}</th>)}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.length === 0 ? (
                                <tr><td colSpan={columns.length}><p className="muted-text" style={{ padding: '1rem' }}>{data.emptyMessage || 'No records found.'}</p></td></tr>
                            ) : rows.map(row => (
                                <tr key={row.id}>
                                    {row.cells.map((cell, index) => <td key={index}>{cell}</td>)}
                                    <td>
                                        <div className="flex gap-2 flex-wrap">
                                            {row.actions?.map(action => (
                                                <a
                                                    key={action.label}
                                                    href={action.href}
                                                    className={`btn btn-sm ${action.variant || 'btn-ghost'}`}
                                                    onClick={action.confirm ? (event) => confirmAction(event, action.confirm) : undefined}
                                                >
                                                    {action.label}
                                                </a>
                                            ))}
                                                                        <a href={`/skills/delete.php?id=${skill.id}`} className="btn btn-danger btn-sm" onClick={(event) => confirmAction(event, 'Delete this skill listing?')}>Delete</a>
                                                                <button type="submit" className="btn btn-danger btn-sm" onClick={(event) => confirmAction(event, 'Decline this swap request?')}>Decline</button>
                                                                    <button type="submit" className="btn btn-danger btn-sm" onClick={(event) => confirmAction(event, 'Cancel this request?')}>Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

function AdminReportsPage({ data }) {
    const reports = data.reports || [];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header">
                <div>
                    <h2 className="section-title">{data.title}</h2>
                    <p className="section-subtitle" style={{ textAlign: 'left' }}>{data.subtitle}</p>
                </div>
            </div>
            <div className="card">
                {reports.length === 0 ? <p className="muted-text">No reports have been submitted yet.</p> : reports.map(report => (
                    <div key={report.id} className="report-card">
                        <div className="report-header">
                            <div><strong>{report.reporterName}</strong> reported <strong>{report.reportedName}</strong> for <em>{report.reason}</em></div>
                            <span className={`report-status report-${report.status}`}>{report.status}</span>
                        </div>
                        <p>{report.description || 'No additional details provided.'}</p>
                        <div className="report-footer">
                            <span className="muted-text">Submitted {report.timeAgo}</span>
                            <div className="flex gap-2">
                                <a href={`?action=reviewed&report_id=${report.id}`} className="btn btn-sm btn-secondary">Mark Reviewed</a>
                                <a href={`?action=resolved&report_id=${report.id}`} className="btn btn-sm btn-primary">Resolve</a>
                                <a href={`?action=dismissed&report_id=${report.id}`} className="btn btn-sm btn-ghost" onClick={(event) => confirmAction(event, 'Dismiss this report?')}>Dismiss</a>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function AdminCategoriesPage({ data }) {
    const categories = data.categories || [];

    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header">
                <div>
                    <h2 className="section-title">{data.title}</h2>
                    <p className="section-subtitle" style={{ textAlign: 'left' }}>{data.subtitle}</p>
                </div>
            </div>
            <div className="card mb-4">
                <form method="POST" action="/admin/categories.php">
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <div className="form-group">
                        <label className="form-label" htmlFor="name">Category Name</label>
                        <input id="name" name="name" type="text" className="form-control" placeholder="e.g. Photography" required />
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="icon">Icon</label>
                        <input id="icon" name="icon" type="text" className="form-control" defaultValue="Book" placeholder="Short label" />
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="color">Color</label>
                        <input id="color" name="color" type="color" className="form-control" defaultValue="#6C63FF" />
                    </div>
                    <button type="submit" className="btn btn-primary">Add Category</button>
                </form>
            </div>

            <div className="card">
                <h3>Existing Categories</h3>
                <div className="table-wrapper">
                    <table className="admin-table">
                        <thead>
                            <tr><th>Icon</th><th>Name</th><th>Color</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            {categories.map(category => (
                                <tr key={category.id}>
                                    <td>{category.icon}</td>
                                    <td>{category.name}</td>
                                    <td><span className="color-swatch" style={{ background: category.color }} /> {category.color}</td>
                                    <td><a href={`?delete_id=${category.id}`} className="btn btn-sm btn-danger" onClick={(event) => confirmAction(event, 'Delete this category?')}>Delete</a></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

function AdminAnalyticsPage({ data }) {
    const topUsers = data.topUsers || [];
    const topReported = data.topReported || [];
    const categoryCounts = data.categoryCounts || [];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header">
                <div>
                    <h2 className="section-title">{data.title}</h2>
                    <p className="section-subtitle" style={{ textAlign: 'left' }}>{data.subtitle}</p>
                </div>
            </div>

            <div className="dashboard-grid">
                <div className="stat-card"><h3>{topUsers.length}</h3><p>Top contributors</p></div>
                <div className="stat-card"><h3>{topReported.length}</h3><p>Reported users</p></div>
                <div className="stat-card"><h3>{categoryCounts.length}</h3><p>Categories tracked</p></div>
            </div>

            <div className="card" style={{ marginTop: '1.5rem' }}>
                <h3>Top Active Users</h3>
                <ul className="list-simple">
                    {topUsers.map(user => <li key={user.id}>{user.name} - {user.skillCount} skills · {user.ratingStars} ({user.totalReviews} reviews)</li>)}
                </ul>
            </div>

            <div className="card" style={{ marginTop: '1.5rem' }}>
                <h3>Most Reported Users</h3>
                {topReported.length === 0 ? <p className="muted-text">No repeated reports yet.</p> : (
                    <ul className="list-simple">
                        {topReported.map(user => <li key={user.id}>{user.name} - {user.reports} reports</li>)}
                    </ul>
                )}
            </div>

            <div className="card" style={{ marginTop: '1.5rem' }}>
                <h3>Category Activity</h3>
                <ul className="list-simple">
                    {categoryCounts.map(category => <li key={category.name}>{category.name} - {category.total} skills listed</li>)}
                </ul>
            </div>
        </div>
    );
}

function SmartMatchPage({ data }) {
    const results = data.results || [];

    return (
        <div className="container" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="section-header">
                <div>
                    <h2 className="section-title">Smart Matches</h2>
                    <p className="section-subtitle" style={{ textAlign: 'left' }}>People who can teach what you want and want to learn what you can teach.</p>
                </div>
                <div className="flex gap-2">
                    <a href="/skills/add.php" className="btn btn-secondary btn-sm">Add Skill</a>
                    <a href="/wanted/add.php" className="btn btn-secondary btn-sm">Add Want</a>
                </div>
            </div>

            <div className="card mb-3">
                <div style={{ display: 'flex', gap: '1rem', alignItems: 'flex-start', flexWrap: 'wrap' }}>
                    <div style={{ fontSize: '2rem' }}>Match</div>
                    <div>
                        <h4 style={{ margin: '0 0 0.35rem' }}>How matching works</h4>
                        <p style={{ margin: 0, color: 'var(--text-muted)', fontSize: '0.88rem' }}>We match people who offer skills you want and want skills you can teach. That gives you a true mutual exchange.</p>
                    </div>
                </div>
            </div>

            {results.length === 0 ? (
                <div className="empty-state">
                    <div className="empty-icon">Search</div>
                    <h3>No mutual matches found yet</h3>
                    <p>Try adding more skills or skill wants to broaden your matchable pool.</p>
                </div>
            ) : (
                <div className="skills-grid">
                    {results.map(result => (
                        <div key={result.id} className="card match-card">
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.85rem', marginBottom: '1rem' }}>
                                <a href={`/profile/view.php?id=${result.id}`}>
                                    <img src={result.avatar} alt={result.name} style={{ width: 52, height: 52, borderRadius: '50%' }} />
                                </a>
                                <div>
                                    <a href={`/profile/view.php?id=${result.id}`} style={{ fontWeight: 700, color: 'var(--text)', textDecoration: 'none' }}>{result.name}</a>
                                    <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>{result.collegeName}</div>
                                    <div style={{ fontSize: '0.8rem', marginTop: 2 }}>{result.ratingStars} <span style={{ color: 'var(--text-dim)', fontSize: '0.75rem' }}>({result.totalReviews})</span></div>
                                </div>
                                {result.distanceBadge ? <div style={{ marginLeft: 'auto' }}><span className="badge badge-nearby">{result.distanceBadge}</span></div> : null}
                            </div>
                            <div style={{ fontSize: '0.8rem', fontWeight: 600, color: '#10B981', marginBottom: '0.3rem' }}>Can teach you:</div>
                            <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', margin: '0 0 0.75rem' }}>{result.theyOffer}</p>
                            {result.distanceKm ? <div style={{ fontSize: '0.78rem', color: 'var(--text-dim)', marginBottom: '0.75rem' }}>Distance: {result.distanceKm} km</div> : null}
                            <a href={`/skills/explore.php?user_id=${result.id}`} className="btn btn-primary btn-sm" style={{ width: '100%' }}>View Their Skills</a>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

function WantedAddPage({ data }) {
    const categories = data.categories || [];
    const wants = data.wants || [];
    const existingCategoryIds = new Set((data.existingCategoryIds || []).map(Number));

    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <div className="page-header">
                <h1 className="page-title">Add a Skill You Want</h1>
                <p className="page-subtitle">Tell others what you want to learn — this powers the Smart Match engine</p>
            </div>

            {data.flash ? <div className={`alert alert-${data.flash.type || 'info'}`}>{data.flash.message}</div> : null}

            <div className="card">
                <form method="POST" action={data.action || '/wanted/add.php'}>
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <div className="form-group">
                        <label className="form-label" htmlFor="category_id">Skill Category I Want to Learn *</label>
                        <select id="category_id" name="category_id" className="form-control" required defaultValue="">
                            <option value="">— Choose category —</option>
                            {categories.map(cat => (
                                <option key={cat.id} value={cat.id} disabled={existingCategoryIds.has(Number(cat.id))}>
                                    {cat.name}{existingCategoryIds.has(Number(cat.id)) ? ' (already added)' : ''}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="description">What specifically do you want to learn? <span className="text-dim">(optional)</span></label>
                        <textarea id="description" name="description" className="form-control" rows="2" maxLength="300" placeholder="e.g. I want to learn Figma from scratch for mobile app design"></textarea>
                    </div>
                    <button type="submit" className="btn btn-primary btn-lg">Add to My Wants</button>
                </form>
            </div>

            {wants.length > 0 ? (
                <div className="card mt-3">
                    <h3 className="card-title mb-2">Your Current Wants</h3>
                    <div className="flex flex-wrap gap-1">
                        {wants.map(want => (
                            <div key={want.id} className="flex-center gap-1" style={{ background: 'rgba(255,101,132,0.1)', border: '1px solid rgba(255,101,132,0.2)', padding: '0.4rem 0.85rem', borderRadius: 20, fontSize: '0.85rem' }}>
                                {want.icon ? `${want.icon} ` : ''}{want.name}
                                <a href={`/wanted/remove.php?id=${want.id}`} style={{ color: '#94A3B8', marginLeft: '0.25rem', fontSize: '1rem', lineHeight: 1 }} title="Remove">×</a>
                            </div>
                        ))}
                    </div>
                </div>
            ) : null}
        </div>
    );
}

function SessionDetailPage({ data }) {
    const session = data.session || {};
    const statusBadge = {
        scheduled: 'badge-pending',
        completed: 'badge-completed',
        cancelled: 'badge-cancelled',
        no_show: 'badge-declined',
    };

    return (
        <div className="container-md" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <h1 className="page-title mb-1">Session with {data.otherName}</h1>
            <p className="text-muted mb-3">{data.reqSkill} ⇄ {data.recSkill}</p>

            {data.meetupLink ? (
                <div className="meetup-banner">
                    <div>
                        <strong>Suggested meet point</strong>
                        <span className="text-muted" style={{ fontSize: '0.88rem', marginLeft: '0.5rem' }}>
                            Halfway between both of you — {data.distance} km apart
                        </span>
                    </div>
                    <a href={data.meetupLink} target="_blank" rel="noopener noreferrer" className="btn btn-secondary btn-sm">Open in Google Maps</a>
                </div>
            ) : null}

            <div className="grid-2" style={{ gap: '1.5rem' }}>
                <div className="card">
                    <h3 className="card-title mb-3">Schedule Session</h3>
                    <form method="POST" action={data.action || '/sessions/detail.php'}>
                        {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                        <div className="form-group">
                            <label className="form-label">Date</label>
                            <input type="date" name="session_date" className="form-control" min={data.minDate || ''} defaultValue={session.scheduledDate || ''} />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Time</label>
                            <input type="time" name="session_time" className="form-control" defaultValue={session.scheduledTime || ''} />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Meeting Link (Google Meet / Zoom)</label>
                            <input type="url" name="meet_link" className="form-control" placeholder="https://meet.google.com/..." defaultValue={session.meetLink || ''} />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Physical Location</label>
                            <input type="text" name="meet_location" className="form-control" placeholder="Library 2nd floor, Canteen, etc." defaultValue={session.meetLocation || ''} />
                        </div>
                        <button type="submit" name="schedule" className="btn btn-primary">Save Schedule</button>
                    </form>
                </div>

                <div>
                    <div className="card mb-2">
                        <h3 className="card-title mb-2">Session Status</h3>
                        <span className={`badge ${statusBadge[session.status] || 'badge-pending'}`}>{session.statusLabel || 'Scheduled'}</span>
                        {session.scheduledDateTime ? (
                            <div className="mt-2" style={{ fontSize: '0.88rem', color: '#94A3B8' }}>{session.scheduledDateTime}</div>
                        ) : null}
                        {session.meetLink ? (
                            <div className="mt-2">
                                <a href={session.meetLink} target="_blank" rel="noopener noreferrer" className="btn btn-success btn-sm">Join Meeting</a>
                            </div>
                        ) : null}
                    </div>

                    {session.status === 'scheduled' ? (
                        <div className="card mb-2">
                            <h3 style={{ fontSize: '0.95rem', fontWeight: 700, marginBottom: '1rem' }}>Mark Session Outcome</h3>
                            <form method="POST" action={data.action || '/sessions/detail.php'}>
                                {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                                <button type="submit" name="complete" className="btn btn-success btn-block btn-sm mb-2" data-confirm="Mark this session as completed?">Mark as Completed</button>
                            </form>
                                <a href={data.reportUrl} className="btn btn-danger btn-block btn-sm">Report No-Show</a>
                        </div>
                    ) : null}

                    <div className="card">
                            <a href={data.chatUrl} className="btn btn-primary btn-block">Open Chat</a>
                    </div>
                </div>
            </div>
        </div>
    );
}

function ReportSubmitPage({ data }) {
    const [reason, setReason] = useState(data.defaultReason || 'no_show');

    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <h2 className="section-title">Report {data.reportedName}</h2>
            <div className="card">
                <form method="POST" action={data.action || '/reports/submit.php'}>
                    {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                    <input type="hidden" name="reported_id" value={data.reportedId} />
                    <input type="hidden" name="swap_id" value={data.swapId} />
                    <div className="form-group">
                        <label className="form-label">Reason</label>
                        {(data.reasonOptions || []).map(option => (
                            <label key={option.value} className="radio-option" style={{ display: 'flex', alignItems: 'flex-start', gap: '0.75rem', marginBottom: '0.75rem' }}>
                                <input type="radio" name="reason" value={option.value} checked={reason === option.value} onChange={() => setReason(option.value)} style={{ marginTop: '0.2rem' }} />
                                <span>{option.label}</span>
                            </label>
                        ))}
                    </div>
                    <div className="form-group">
                        <label className="form-label" htmlFor="description">Details</label>
                        <textarea id="description" name="description" className="form-control" rows="5" placeholder="Tell us what happened. This helps us investigate the report."></textarea>
                    </div>
                    <div className="flex gap-2">
                        <button type="submit" className="btn btn-danger">Submit Report</button>
                        <a href={data.cancelUrl || `/profile/view.php?id=${data.reportedId}`} className="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    );
}

function ReviewSubmitPage({ data }) {
    const [rating, setRating] = useState(Number(data.existingRating || 0));
    const ratingLabels = {
        1: 'Terrible',
        2: 'Poor',
        3: 'Average',
        4: 'Good',
        5: 'Excellent',
    };

    return (
        <div className="container-sm" style={{ paddingTop: '2rem', paddingBottom: '3rem' }}>
            <h2 className="section-title">Leave a Review</h2>

            {data.existing ? <div className="alert alert-success">You have already reviewed this swap.</div> : null}

            {data.existing ? (
                <a href={data.backUrl || '/swaps/my_requests.php'} className="btn btn-secondary">Back to Swaps</a>
            ) : (
                <>
                    <div className="card mb-3">
                        <div style={{ textAlign: 'center', padding: '1.5rem 1rem' }}>
                            <img src={data.revieweeAvatar} alt={data.revieweeName} style={{ width: 80, height: 80, borderRadius: '50%', marginBottom: '0.75rem' }} />
                            <h3 style={{ margin: '0 0 0.25rem' }}>{data.revieweeName}</h3>
                            <div style={{ color: '#94A3B8', fontSize: '0.85rem' }}>{ratingStars(data.revieweeRating)} &nbsp;({data.revieweeTotalReviews} reviews)</div>
                            <div style={{ marginTop: '0.5rem', fontSize: '0.85rem', color: '#94A3B8' }}>
                                Swap: <strong>{data.reqSkill}</strong> ⇄ <strong>{data.recSkill}</strong>
                            </div>
                        </div>
                    </div>

                    <div className="card">
                        <form method="POST" action={data.action || '/reviews/submit.php'}>
                            {data.csrfToken ? <input type="hidden" name="csrf_token" value={data.csrfToken} /> : null}
                            <input type="hidden" name="swap_id" value={data.swapId} />
                            <input type="hidden" name="reviewee_id" value={data.revieweeId} />

                            <div className="form-group">
                                <label className="form-label">Your Rating *</label>
                                <div className="star-select" id="starSelect">
                                    {[5,4,3,2,1].map(value => (
                                        <React.Fragment key={value}>
                                            <input type="radio" name="rating" id={`star${value}`} value={value} checked={rating === value} onChange={() => setRating(value)} />
                                            <label htmlFor={`star${value}`} title={`${value} star${value > 1 ? 's' : ''}`}>★</label>
                                        </React.Fragment>
                                    ))}
                                </div>
                                <div id="ratingLabel" style={{ fontSize: '0.82rem', color: '#94A3B8', marginTop: '0.35rem' }}>{ratingLabels[rating] || data.ratingLabel || 'Click to rate'}</div>
                            </div>

                            <div className="form-group">
                                <label className="form-label" htmlFor="comment">Comment <span style={{ color: '#64748B' }}>(optional)</span></label>
                                <textarea id="comment" name="comment" className="form-control" rows="4" placeholder="Share your experience with this skill swap…"></textarea>
                            </div>

                            <div className="flex gap-2">
                                <button type="submit" className="btn btn-primary">Submit Review</button>
                                <a href={data.backUrl || '/swaps/my_requests.php'} className="btn btn-ghost">Cancel</a>
                            </div>
                        </form>
                    </div>
                </>
            )}
        </div>
    );
}

const root = document.getElementById('app-root');
if (root && window.__SKILLSWAP_PAGE__) {
    ReactDOM.createRoot(root).render(<App />);
}
