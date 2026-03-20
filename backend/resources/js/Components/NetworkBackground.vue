<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    color: { type: String, default: 'rgba(255, 255, 255, 0.4)' },
    count: { type: Number, default: 50 },
    distance: { type: Number, default: 180 },
    speed: { type: Number, default: 0.5 },
    opacity: { type: Number, default: 0.4 }
});

const canvasRef = ref(null);
let ctx = null;
let animationFrameId = null;
let particles = [];

class Particle {
    constructor(w, h) {
        this.x = Math.random() * w;
        this.y = Math.random() * h;
        this.vx = (Math.random() - 0.5) * props.speed;
        this.vy = (Math.random() - 0.5) * props.speed;
        this.r = Math.random() * 1.5 + 0.5;
    }
    update(w, h) {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > w) this.vx = -this.vx;
        if (this.y < 0 || this.y > h) this.vy = -this.vy;
    }
    draw(ctx) {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        ctx.fillStyle = props.color;
        ctx.fill();
    }
}

const resize = () => {
    if(!canvasRef.value) return;
    const parent = canvasRef.value.parentElement;
    canvasRef.value.width = parent.offsetWidth;
    canvasRef.value.height = parent.offsetHeight;
};

const animate = () => {
    if(!ctx || !canvasRef.value) return;
    const w = canvasRef.value.width;
    const h = canvasRef.value.height;
    ctx.clearRect(0, 0, w, h);

    particles.forEach((p, i) => {
        p.update(w, h);
        p.draw(ctx);
        for (let j = i + 1; j < particles.length; j++) {
            const p2 = particles[j];
            const dx = p.x - p2.x;
            const dy = p.y - p2.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            
            if (dist < props.distance) {
                const alpha = (1 - dist / props.distance) * props.opacity;
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(p2.x, p2.y);
                
                // Use the color's RGB with the calculated alpha
                if (props.color.startsWith('rgba')) {
                    // Quick and dirty alpha replacement for rgba
                    const colorParts = props.color.split(',');
                    colorParts[3] = ` ${alpha})`;
                    ctx.strokeStyle = colorParts.join(',');
                } else {
                    ctx.strokeStyle = `rgba(255, 255, 255, ${alpha})`;
                }
                
                ctx.lineWidth = 0.5;
                ctx.stroke();
            }
        }
    });

    animationFrameId = requestAnimationFrame(animate);
};

onMounted(() => {
    ctx = canvasRef.value.getContext('2d');
    resize();
    for (let i = 0; i < props.count; i++) {
        particles.push(new Particle(canvasRef.value.width, canvasRef.value.height));
    }
    window.addEventListener('resize', resize);
    animate();
});

onUnmounted(() => {
    cancelAnimationFrame(animationFrameId);
    window.removeEventListener('resize', resize);
});
</script>

<template>
    <canvas ref="canvasRef" class="pointer-events-none" />
</template>
