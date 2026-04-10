import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        count: { type: Number, default: 0 },
    };

    connect() {
        // Subtle pulse animation on the container
        this.element.style.animation = 'social-proof-pulse 2s ease-in-out infinite';

        if (!document.getElementById('social-proof-pulse-style')) {
            const style = document.createElement('style');
            style.id = 'social-proof-pulse-style';
            style.textContent = `
                @keyframes social-proof-pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.7; }
                }
            `;
            document.head.appendChild(style);
        }
    }
}
