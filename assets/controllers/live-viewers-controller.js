import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['count'];
    static values = {
        url: String,
        interval: { type: Number, default: 30000 },
    };

    connect() {
        this.poll();
        this.timer = setInterval(() => this.poll(), this.intervalValue);
    }

    disconnect() {
        if (this.timer) clearInterval(this.timer);
    }

    async poll() {
        try {
            const response = await fetch(this.urlValue);
            if (!response.ok) return;
            const data = await response.json();
            if (this.hasCountTarget) {
                this.countTarget.style.transition = 'opacity 0.3s';
                this.countTarget.style.opacity = '0.4';
                setTimeout(() => {
                    this.countTarget.textContent = data.count;
                    this.countTarget.style.opacity = '1';
                }, 150);
            }
        } catch (e) {
            // Silently fail — widget is non-critical
        }
    }
}
