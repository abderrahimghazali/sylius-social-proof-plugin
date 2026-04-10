import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['notification', 'message', 'image', 'time'];
    static values = {
        url: String,
        displayInterval: { type: Number, default: 8000 },
        max: { type: Number, default: 5 },
        style: { type: String, default: 'toast' },
    };

    connect() {
        this.purchases = [];
        this.currentIndex = 0;
        this.isVisible = false;
        this.fetchAndStart();
    }

    disconnect() {
        if (this.showTimer) clearTimeout(this.showTimer);
        if (this.hideTimer) clearTimeout(this.hideTimer);
    }

    async fetchAndStart() {
        try {
            const response = await fetch(this.urlValue);
            if (!response.ok) return;
            this.purchases = await response.json();
            if (this.purchases.length > 0) {
                this.showTimer = setTimeout(
                    () => this.showNext(),
                    3000,
                );
            }
        } catch (e) {
            // Non-critical
        }
    }

    showNext() {
        if (this.currentIndex >= this.purchases.length || this.currentIndex >= this.maxValue) {
            return;
        }

        const purchase = this.purchases[this.currentIndex];
        const el = this.notificationTarget;

        // Set content
        const city = purchase.city ? ` from ${purchase.city}` : '';
        const name = purchase.first_name || 'Someone';
        this.messageTarget.textContent = `${name}${city} just bought ${purchase.product_name}`;

        if (this.hasImageTarget && purchase.product_image) {
            this.imageTarget.src = '/media/image/' + purchase.product_image;
            this.imageTarget.style.display = 'block';
        } else if (this.hasImageTarget) {
            this.imageTarget.style.display = 'none';
        }

        if (this.hasTimeTarget) {
            this.timeTarget.textContent = this.timeAgo(purchase.purchased_at);
        }

        // Show
        el.style.display = 'block';
        requestAnimationFrame(() => {
            el.style.transform = 'translateY(0)';
            el.style.opacity = '1';
        });
        this.isVisible = true;

        // Auto-hide after display interval
        this.hideTimer = setTimeout(() => {
            this.hideNotification(() => {
                this.currentIndex++;
                this.showTimer = setTimeout(
                    () => this.showNext(),
                    2000,
                );
            });
        }, this.displayIntervalValue);
    }

    dismiss() {
        if (this.hideTimer) clearTimeout(this.hideTimer);
        this.hideNotification(() => {
            this.currentIndex++;
        });
    }

    hideNotification(callback) {
        const el = this.notificationTarget;
        const style = this.styleValue;

        if (style === 'toast') {
            el.style.transform = 'translateY(20px)';
        } else if (style === 'bottom_bar') {
            el.style.transform = 'translateY(100%)';
        } else {
            el.style.transform = 'translateY(-100%)';
        }
        el.style.opacity = '0';
        this.isVisible = false;

        setTimeout(() => {
            el.style.display = 'none';
            if (callback) callback();
        }, 400);
    }

    timeAgo(dateStr) {
        if (!dateStr) return '';
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 60000);
        if (diff < 1) return 'just now';
        if (diff < 60) return `${diff}m ago`;
        const hours = Math.floor(diff / 60);
        if (hours < 24) return `${hours}h ago`;
        return `${Math.floor(hours / 24)}d ago`;
    }
}
