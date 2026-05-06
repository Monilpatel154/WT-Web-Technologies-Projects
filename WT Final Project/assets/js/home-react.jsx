const { createElement: h, useEffect, useRef } = React;

(function () {
    const data = window.__SKILLSWAP_HOME__ || {};
    const root = document.getElementById('home-app');
    if (!root) return;

    const stats     = data.stats     || {};
    const categories = Array.isArray(data.categories) ? data.categories : [];
    const featured   = Array.isArray(data.featured)   ? data.featured   : [];

    /* ── Animated counter ── */
    function AnimatedNumber({ target }) {
        const ref = useRef(null);
        useEffect(() => {
            let start = 0;
            const end = parseInt(target) || 0;
            if (end === 0) return;
            const dur = 1200;
            const step = Math.ceil(dur / end);
            const timer = setInterval(() => {
                start += Math.max(1, Math.floor(end / 40));
                if (start >= end) { start = end; clearInterval(timer); }
                if (ref.current) ref.current.textContent = start + '+';
            }, step);
            return () => clearInterval(timer);
        }, [target]);
        return h('span', { ref }, (parseInt(target) || 0) + '+');
    }

    /* ── Stars ── */
    function Stars({ rating }) {
        const total = Math.max(0, Math.min(5, Math.round(Number(rating) || 0)));
        return h('span', { className: 'stars', style: { fontSize: '0.75rem' } },
            Array.from({ length: 5 }, (_, i) =>
                h('span', { key: i, className: i < total ? 'star-on' : 'star-off' },
                    i < total ? '★' : '☆')
            )
        );
    }

    /* ── Hero CTA ── */
    function HeroCta() {
        if (data.loggedIn) {
            return h('div', { className: 'hero-actions' },
                h('a', { href: '/match/smart_match.php', className: 'btn btn-primary btn-lg' },
                    h('span', null, '⚡'), ' Find My Matches'),
                h('a', { href: '/skills/add.php', className: 'btn btn-ghost btn-lg' },
                    h('span', null, '＋'), ' Add a Skill')
            );
        }
        return h('div', { className: 'hero-actions' },
            h('a', { href: '/auth/register.php', className: 'btn btn-primary btn-lg' },
                h('span', null, '🚀'), ' Get Started Free'),
            h('a', { href: '/skills/explore.php', className: 'btn btn-ghost btn-lg' },
                'Explore Skills →')
        );
    }

    /* ── Stat Item ── */
    function StatItem({ value, label, suffix }) {
        return h('div', { className: 'stat-item' },
            h('div', { className: 'stat-num' },
                value > 0 ? h(AnimatedNumber, { target: value }) : '—'
            ),
            h('div', { className: 'stat-label' }, label)
        );
    }

    /* ── Step Card for "How it works" ── */
    function StepCard({ num, icon, title, desc }) {
        return h('div', { className: 'card', style: { textAlign: 'center', padding: '2.5rem 1.5rem' } },
            h('div', { style: {
                width: '56px', height: '56px', borderRadius: '16px', margin: '0 auto 1.25rem',
                background: 'linear-gradient(135deg, rgba(124,58,237,0.25), rgba(236,72,153,0.15))',
                border: '1px solid rgba(124,58,237,0.25)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: '1.6rem'
            }}, icon),
            h('div', { style: {
                display: 'inline-block', fontSize: '0.72rem', fontWeight: '800',
                letterSpacing: '0.08em', textTransform: 'uppercase',
                color: '#A78BFA', marginBottom: '0.75rem',
                background: 'rgba(124,58,237,0.1)', border: '1px solid rgba(124,58,237,0.2)',
                padding: '0.2rem 0.65rem', borderRadius: '999px'
            }}, `Step ${num}`),
            h('h3', { style: { fontSize: '1.1rem', fontWeight: '800', marginBottom: '0.6rem', letterSpacing: '-0.02em' } }, title),
            h('p', { className: 'text-muted', style: { fontSize: '0.9rem', lineHeight: '1.65' } }, desc)
        );
    }

    /* ── Category Card ── */
    function CategoryCard({ category }) {
        return h('a',
            { href: `/skills/explore.php?category=${category.id}`, className: 'category-card' },
            h('span', { className: 'category-icon' }, category.icon || '📚'),
            h('div', { className: 'category-name' }, category.name),
            h('div', { className: 'category-count' }, `${category.count} skill${category.count === 1 ? '' : 's'}`)
        );
    }

    /* ── Skill Card ── */
    function FeaturedCard({ skill }) {
        return h('a',
            { href: `/skills/detail.php?id=${skill.id}`, className: 'skill-card', style: { textDecoration: 'none', color: 'inherit' } },
            h('div', { className: 'skill-card-body' },
                h('div', { className: 'skill-category-tag' }, `${skill.categoryIcon || ''} ${skill.category}`),
                h('div', { className: 'skill-title' }, skill.title),
                h('div', { className: 'skill-desc' }, skill.description),
                h('div', { className: 'skill-meta' },
                    h('div', { className: 'skill-user' },
                        h('img', { src: skill.avatar, alt: skill.userName }),
                        h('span', null, skill.userName),
                        h(Stars, { rating: skill.rating })
                    )
                )
            ),
            h('div', { className: 'skill-card-footer' },
                h('span', { className: 'credit-badge' }, `💎 ${skill.creditValue} credit${skill.creditValue === 1 ? '' : 's'}`),
                h('span', { className: `badge badge-${skill.mode}` }, skill.modeLabel)
            )
        );
    }

    /* ── App ── */
    function App() {
        return h(React.Fragment, null,

            /* HERO */
            h('section', { className: 'hero' },
                h('div', { className: 'container' },
                    h('div', { className: 'hero-content fade-up' },

                        h('div', { className: 'hero-badge' }, 'Skill exchange platform for students'),

                        h('h1', { className: 'hero-title' },
                            'Teach what you know.',
                            h('span', { className: 'highlight' }, 'Learn what you need.')
                        ),

                        h('p', { className: 'hero-subtitle' },
                            'SkillSwap connects students to exchange skills — no money, just knowledge. Build your profile, find your match, and grow together.'
                        ),

                        h(HeroCta),

                        h('div', { className: 'hero-stats' },
                            h(StatItem, { value: stats.totalUsers     || 0, label: 'Students' }),
                            h(StatItem, { value: stats.totalSkills    || 0, label: 'Skills Listed' }),
                            h(StatItem, { value: stats.totalSwaps     || 0, label: 'Swaps Done' }),
                            h('div', { className: 'stat-item' },
                                h('div', { className: 'stat-num' }, stats.totalCategories || 0),
                                h('div', { className: 'stat-label' }, 'Categories')
                            )
                        )
                    )
                )
            ),

            /* CATEGORIES */
            h('section', { className: 'section section-alt' },
                h('div', { className: 'container' },
                    h('div', { className: 'section-header' },
                        h('h2', { className: 'section-title' }, 'Browse by Category'),
                        h('p', { className: 'section-subtitle' },
                            'Explore every domain — find the right people to teach or learn from.')
                    ),
                    h('div', { className: 'category-grid' },
                        categories.map(cat => h(CategoryCard, { key: cat.id, category: cat }))
                    )
                )
            ),

            /* FEATURED SKILLS */
            h('section', { className: 'section' },
                h('div', { className: 'container' },
                    h('div', { className: 'flex-between mb-3' },
                        h('div', null,
                            h('h2', { className: 'section-title', style: { textAlign: 'left', marginBottom: '0.3rem' } }, 'Latest Skills'),
                            h('p', { className: 'text-muted' }, 'Fresh listings from students on campus')
                        ),
                        h('a', { href: '/skills/explore.php', className: 'btn btn-ghost' }, 'View all →')
                    ),
                    h('div', { className: 'skills-grid' },
                        featured.map(skill => h(FeaturedCard, { key: skill.id, skill }))
                    )
                )
            ),

            /* HOW IT WORKS */
            h('section', { className: 'section section-alt' },
                h('div', { className: 'container' },
                    h('div', { className: 'section-header' },
                        h('h2', { className: 'section-title' }, 'How It Works'),
                        h('p', { className: 'section-subtitle' }, 'Simple flow from profile to first skill exchange.')
                    ),
                    h('div', { className: 'grid-3' },
                        h(StepCard, {
                            num: 1, icon: '✏️',
                            title: 'Add Your Skills',
                            desc: 'List what you can teach and what you want to learn from others on campus.'
                        }),
                        h(StepCard, {
                            num: 2, icon: '🔍',
                            title: 'Find a Match',
                            desc: 'Our smart match algorithm finds the best skill partners based on mutual needs.'
                        }),
                        h(StepCard, {
                            num: 3, icon: '🤝',
                            title: 'Start Swapping',
                            desc: 'Agree on a session time, meet up or go online, and exchange knowledge.'
                        })
                    )
                )
            ),

            /* CTA */
            !data.loggedIn && h('section', { className: 'section' },
                h('div', { className: 'container-sm' },
                    h('div', {
                        className: 'cta-card',
                        style: {
                            background: 'rgba(14,18,32,0.85)',
                            border: '1px solid rgba(124,58,237,0.25)',
                            borderRadius: '24px',
                            padding: '4rem 2rem',
                            textAlign: 'center',
                            backdropFilter: 'blur(20px)',
                            position: 'relative',
                            overflow: 'hidden'
                        }
                    },
                        h('div', { style: {
                            position: 'absolute', inset: 0,
                            background: 'radial-gradient(ellipse 80% 60% at 50% 0%, rgba(124,58,237,0.15), transparent)',
                            pointerEvents: 'none'
                        }}),
                        h('div', { style: {
                            display: 'inline-block', fontSize: '0.78rem', fontWeight: '800',
                            letterSpacing: '0.08em', textTransform: 'uppercase',
                            color: '#C4B5FD', marginBottom: '1.25rem',
                            background: 'rgba(124,58,237,0.12)', border: '1px solid rgba(124,58,237,0.25)',
                            padding: '0.35rem 1rem', borderRadius: '999px'
                        }}, '✦ Join the community'),
                        h('h2', { style: { fontSize: '2.2rem', fontWeight: '900', letterSpacing: '-0.04em', marginBottom: '1rem' } },
                            'Ready to Start Swapping?'),
                        h('p', { className: 'text-muted', style: { fontSize: '1.05rem', marginBottom: '2rem', lineHeight: '1.7' } },
                            'Join hundreds of students already exchanging skills on campus. It\'s free, it\'s fun, it\'s powerful.'),
                        h('a', { href: '/auth/register.php', className: 'btn btn-primary btn-lg' },
                            '🚀 Create Free Account')
                    )
                )
            )
        );
    }

    ReactDOM.createRoot(root).render(h(App));
})();
