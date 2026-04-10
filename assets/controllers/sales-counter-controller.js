import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['count'];
    static values = {
        count: { type: Number, default: 0 },
    };

    connect() {
        this.animateCount();
    }

    animateCount() {
        const target = this.countValue;
        const duration = 1000;
        const start = performance.now();

        const step = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            const current = Math.round(eased * target);

            if (this.hasCountTarget) {
                this.countTarget.textContent = current;
            }

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    }
}
