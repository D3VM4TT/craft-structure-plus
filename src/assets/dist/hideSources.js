document.addEventListener("DOMContentLoaded", function () {
    if (!window.hiddenSections || !Array.isArray(window.hiddenSections)) {
        return;
    }

    // Loop through each section handle and hide matching sidebar items
    window.hiddenSections.forEach((handle) => {
        let sourceItem = document.querySelector(`a[data-handle="${handle}"]`);
        if (sourceItem) {
            sourceItem.closest("li").classList.add("hidden");
        }
    });
});
