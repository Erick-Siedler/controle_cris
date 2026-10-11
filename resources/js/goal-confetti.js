export const celebrateGoal = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const canvas = document.createElement('canvas');
    canvas.setAttribute('aria-hidden', 'true');
    canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:10000;';
    const context = canvas.getContext('2d');

    if (!context) {
        return;
    }

    document.body.appendChild(canvas);
    let width;
    let height;
    const resize = () => {
        width = window.innerWidth;
        height = window.innerHeight;
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
    };
    resize();
    window.addEventListener('resize', resize);

    const colors = ['#a855f7', '#ec4899', '#38bdf8', '#fbbf24', '#34d399', '#f43f5e'];
    const particles = Array.from({ length: 480 }, (_, index) => ({
        origin: [0.15, 0.5, 0.85][index % 3],
        delay: Math.random() * 0.9,
        velocityX: (Math.random() - 0.5) * 0.3,
        velocityY: -(0.85 + Math.random() * 0.3),
        size: 4 + Math.random() * 5,
        rotation: Math.random() * Math.PI,
        spin: (Math.random() - 0.5) * 12,
        color: colors[index % colors.length],
    }));
    const started = performance.now();
    let frame;
    const cleanup = () => {
        cancelAnimationFrame(frame);
        window.removeEventListener('resize', resize);
        window.removeEventListener('pagehide', cleanup);
        canvas.remove();
    };
    const draw = (now) => {
        const elapsed = (now - started) / 1000;
        context.clearRect(0, 0, width, height);

        if (elapsed > 5.5) {
            cleanup();
            return;
        }

        particles.forEach((particle) => {
            const age = elapsed - particle.delay;
            if (age < 0) return;

            const x = width * (particle.origin + particle.velocityX * age);
            const y = height + 12 + height * (particle.velocityY * age + 0.275 * age * age);
            if (y > height + 20) return;

            context.save();
            context.translate(x, y);
            context.rotate(particle.rotation + particle.spin * age);
            context.scale(1, Math.cos(age * 9 + particle.rotation));
            context.fillStyle = particle.color;
            context.fillRect(-particle.size / 2, -particle.size / 4, particle.size, particle.size / 2);
            context.restore();
        });

        frame = requestAnimationFrame(draw);
    };

    window.addEventListener('pagehide', cleanup);
    frame = requestAnimationFrame(draw);
};
