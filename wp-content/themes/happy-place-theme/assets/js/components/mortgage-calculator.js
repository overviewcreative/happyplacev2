/**
 * Mortgage Calculator Component
 * File: /assets/js/components/mortgage-calculator.js
 * 
 * @package HappyPlaceTheme
 */

(function() {
    'use strict';

    // ============================================
    // MORTGAGE CALCULATOR
    // ============================================
    
    /**
     * Calculate mortgage payment
     */
    window.calculateMortgage = function() {
        const price = parseFloat(document.getElementById('calc-price').value);
        const downPercent = parseFloat(document.getElementById('calc-down').value);
        const rate = parseFloat(document.getElementById('calc-rate').value) / 100 / 12;
        const term = parseFloat(document.getElementById('calc-term').value) * 12;
        
        if (!price || price <= 0) {
            alert('Please enter a valid home price');
            return;
        }
        
        const loanAmount = price * (1 - downPercent / 100);
        const payment = loanAmount * (rate * Math.pow(1 + rate, term)) / (Math.pow(1 + rate, term) - 1);
        
        const resultDiv = document.getElementById('calc-result');
        const amountDiv = document.getElementById('calc-amount');
        
        if (resultDiv && amountDiv) {
            amountDiv.textContent = '$' + Math.round(payment).toLocaleString();
            resultDiv.style.display = 'block';
        }
    };

    // ============================================
    // SOCIAL SHARE FUNCTIONS
    // ============================================
    
    /**
     * Share on Facebook
     */
    window.shareOnFacebook = function() {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href));
    };
    
    /**
     * Share on Twitter
     */
    window.shareOnTwitter = function() {
        window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title));
    };
    
    /**
     * Share on Pinterest
     */
    window.shareOnPinterest = function() {
        const img = document.querySelector('.hph-hero__slide') ? 
            document.querySelector('.hph-hero__slide').style.backgroundImage.slice(5, -2) : '';
        window.open('https://pinterest.com/pin/create/button/?url=' + encodeURIComponent(window.location.href) + 
            '&media=' + encodeURIComponent(img) + '&description=' + encodeURIComponent(document.title));
    };
    
    /**
     * Share via email
     */
    window.shareViaEmail = function() {
        window.location.href = 'mailto:?subject=' + encodeURIComponent(document.title) + 
            '&body=' + encodeURIComponent('Check out this property: ' + window.location.href);
    };
    
    /**
     * Copy link to clipboard
     */
    window.copyLink = function() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            if (typeof showNotification === 'function') {
                showNotification('Link copied to clipboard!');
            } else {
                alert('Link copied to clipboard!');
            }
        }, function() {
            // Fallback for older browsers
            const temp = document.createElement('input');
            document.body.appendChild(temp);
            temp.value = window.location.href;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            
            if (typeof showNotification === 'function') {
                showNotification('Link copied to clipboard!');
            } else {
                alert('Link copied to clipboard!');
            }
        });
    };

})();
