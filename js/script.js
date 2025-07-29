function createParticles() {
    const particlesContainer = document.getElementById('particles');
    if (!particlesContainer) return;
    const particleCount = 50;
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        const size = Math.random() * 4 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 8 + 's';
        particle.style.animationDuration = (Math.random() * 4 + 6) + 's';
        particlesContainer.appendChild(particle);
    }
}

function hideLoader() {
    const loader = document.getElementById('loader');
    loader.classList.add('fade-out');
    setTimeout(() => {
        loader.style.display = 'none';
        document.getElementById('main-content').style.display = 'block';
        document.body.style.overflow = 'auto';
    }, 1000);
}

// Loader sadece ilk ziyaret için
window.addEventListener('load', () => {
    if (!localStorage.getItem('visited')) {
        createParticles();
        setTimeout(() => {
            hideLoader();
            localStorage.setItem('visited', 'true');
        }, 2000);
    } else {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('main-content').style.display = 'block';
        document.body.style.overflow = 'auto';
    }
});

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Scroll animasyonları için Intersection Observer
const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.15 });

document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
});

// Sayılar için sayaç animasyonu
document.querySelectorAll('.stat-number').forEach(stat => {
    const updateCount = () => {
        const target = +stat.getAttribute('data-target');
        const count = +stat.innerText;
        const increment = Math.ceil(target / 100);

        if (count < target) {
            stat.innerText = count + increment;
            setTimeout(updateCount, 20);
        } else {
            stat.innerText = target;
        }
    };

    const statObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateCount();
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.7 });

    statObserver.observe(stat);
});

// Manuel skip (tıklama)
document.addEventListener('click', () => {
    const loader = document.getElementById('loader');
    if (!loader.classList.contains('fade-out')) {
        hideLoader();
    }
});

// Skip (klavye)
document.addEventListener('keydown', (e) => {
    if (e.code === 'Space' || e.code === 'Enter') {
        const loader = document.getElementById('loader');
        if (!loader.classList.contains('fade-out')) {
            hideLoader();
        }
    }
});

document.querySelectorAll('.cta-btn').forEach(btn => {
    btn.style.position = 'relative';
    btn.style.zIndex = 1;
    // Yazı z-index 3 olsun (opsiyonel, garanti için)
    btn.childNodes.forEach(node => {
        if (node.nodeType === 3) return; // text node
        node.style && (node.style.zIndex = 3);
    });
    btn.addEventListener('mouseenter', function(e) {
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
        btn.appendChild(ripple);
        btn.classList.add('ripple-active');
        ripple.addEventListener('animationend', () => {
            ripple.remove();
        });
    });
    btn.addEventListener('mouseleave', function() {
        btn.classList.remove('ripple-active');
    });
    btn.addEventListener('blur', function() {
        btn.classList.remove('ripple-active');
    });
});