export function setupAutocomplete(input, data) {
    input.addEventListener("input", function () {
        const value = this.value.toLowerCase();
        closeSuggestions();

        if (!value) return;

        const suggestions = data.filter(ville =>
            ville.nom.toLowerCase().startsWith(value)
        );

        const container = document.createElement("div");
        container.setAttribute("class", "autocomplete-items position-absolute w-100 bg-white border");
        container.style.zIndex = 999;
        container.style.maxHeight = '200px';
        container.style.overflowY = 'auto';

        suggestions.forEach(ville => {
            const item = document.createElement("div");
            item.className = "p-2 cursor-pointer";
            item.innerHTML = `${ville.nom} (${ville.code_postal})`;
            item.addEventListener("click", () => {
                input.value = `${ville.nom}`;
                closeSuggestions();
            });
            container.appendChild(item);
        });

        input.parentNode.appendChild(container);
    });

    document.addEventListener("click", closeSuggestions);

    function closeSuggestions() {
        const items = document.querySelectorAll(".autocomplete-items");
        items.forEach(item => item.remove());
    }
}
