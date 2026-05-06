// SkillSwap - Main JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // ── Alert Auto-Close ────────────────────────────────────
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // ── Character Counter for Textareas ─────────────────────
    document.querySelectorAll('textarea[maxlength]').forEach(ta => {
        const max   = ta.getAttribute('maxlength');
        const hint  = ta.nextElementSibling;
        if (hint && hint.classList.contains('char-count')) {
            ta.addEventListener('input', () => {
                hint.textContent = `${ta.value.length} / ${max}`;
            });
        }
    });

    // ── Confirm Dialogs ─────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            const msg = el.dataset.confirm || 'Are you sure?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ── Active Nav Link ─────────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href && new URL(link.href).pathname === currentPath) {
            link.style.color = '#8B84FF';
        }
    });

    // ── Star Rating Interactive ─────────────────────────────
    initStarRating();

    // ── Skill Card Fade-In ──────────────────────────────────
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-up');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.skill-card, .match-card').forEach(el => {
        observer.observe(el);
    });

    // ── Distance Slider ─────────────────────────────────────
    const distSlider = document.getElementById('distanceSlider');
    const distLabel  = document.getElementById('distanceLabel');
    if (distSlider && distLabel) {
        distSlider.addEventListener('input', () => {
            distLabel.textContent = distSlider.value + ' km';
        });
    }

    // ── Chat Auto-Scroll ────────────────────────────────────
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
        initChat();
    }

    // ── Notification Poll ───────────────────────────────────
    if (document.querySelector('.notif-badge') !== undefined) {
        setInterval(pollNotifications, 30000);
    }

});

// ── Star Rating ─────────────────────────────────────────────
function initStarRating() {
    const container = document.querySelector('.star-select');
    if (!container) return;
    const inputs = container.querySelectorAll('input');
    const labels = container.querySelectorAll('label');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            const val = parseInt(input.value);
            labels.forEach((label, i) => {
                // labels are reversed in flex-direction: row-reverse
                label.style.color = (inputs.length - i) <= val ? '#F59E0B' : '#334155';
            });
        });
    });
}

// ── AJAX Chat ───────────────────────────────────────────────
function initChat() {
    const chatForm    = document.getElementById('chatForm');
    const chatInput   = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    if (!chatForm) return;

    const swapId = chatForm.dataset.swapId;
    let lastId   = parseInt(chatMessages.dataset.lastId || '0', 10);

    chatForm.addEventListener('submit', async e => {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;
        chatInput.value = '';

        try {
            const fd = new FormData(chatForm);
            const res = await fetch(chatForm.action, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'ok') {
                appendMessage(data.message);
                lastId = data.message.message_id;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } catch (err) { console.error('Chat send error', err); }
    });

    // Poll for new messages every 3s
    setInterval(async () => {
        try {
            const res = await fetch(`/messages/fetch.php?swap_id=${swapId}&after=${lastId}`);
            const data = await res.json();
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(m => {
                    appendMessage(m);
                    lastId = m.message_id;
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } catch (err) {}
    }, 3000);
}

function appendMessage(msg) {
    const chatMessages = document.getElementById('chatMessages');
    const myId = parseInt(chatMessages.dataset.userId, 10);
    const isSent = msg.sender_id === myId;
    const div = document.createElement('div');
    div.className = `message-item${isSent ? ' sent' : ''}`;
    div.innerHTML = `
        <img src="${msg.avatar}" alt="${msg.name}" class="message-avatar">
        <div>
            <div class="message-bubble">${escapeHtml(msg.message)}</div>
            <div class="message-time">${msg.time_ago}</div>
        </div>`;
    chatMessages.appendChild(div);
}

// ── Notification Poll ───────────────────────────────────────
async function pollNotifications() {
    try {
        const res = await fetch('/notifications/count.php');
        const data = await res.json();
        const badge = document.querySelector('.notif-badge');
        if (data.count > 0) {
            if (!badge) {
                const btn = document.querySelector('.notif-btn');
                if (btn) {
                    const span = document.createElement('span');
                    span.className = 'notif-badge';
                    span.textContent = data.count;
                    btn.appendChild(span);
                }
            } else {
                badge.textContent = data.count;
            }
        } else if (badge) {
            badge.remove();
        }
    } catch (e) {}
}

// ── Helpers ─────────────────────────────────────────────────
function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
