document.addEventListener('DOMContentLoaded', function() {
    function getRandomValue(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function applyRandomBorderRadius(img) {
        const values = Array.from({length: 8}, () => getRandomValue(30, 100));
        img.style.borderRadius = `${values[0]}% ${values[1]}% ${values[2]}% ${values[3]}% / ${values[4]}% ${values[5]}% ${values[6]}% ${values[7]}%`;
    }

    // Apply to existing images
    document.querySelectorAll('.wp-block-post-content img').forEach(applyRandomBorderRadius);

    // Handle dynamically loaded images
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1 && node.matches('.wp-block-post-content img')) {
                    applyRandomBorderRadius(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
