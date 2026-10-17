
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("idolSearch");
    const priceFilter = document.getElementById("priceFilter");
    const sortSelect = document.getElementById("idolSort");
    const grid = document.getElementById("idolGrid");
    const count = document.getElementById("idolCount");
    const noResults = document.getElementById("noIdolsMessage");

    if (!searchInput || !priceFilter || !sortSelect || !grid || !count || !noResults) {
        console.error("Catalogue elements missing. Check idols.php.");
        return;
    }

    const cards = Array.from(grid.querySelectorAll(".idol-card"));

    function updateCatalogue() {
        const searchText = searchInput.value.trim().toLowerCase();
        const selectedPrice = priceFilter.value;
        const sortBy = sortSelect.value;

        // Highlight only when search or price filter is active.
        const filterIsActive =
            searchText !== "" || selectedPrice !== "all";

        const matchingCards = cards.filter(function (card) {
            const name = (card.getAttribute("data-name") || "").toLowerCase();
            const description =
                (card.getAttribute("data-description") || "").toLowerCase();
            const price = parseFloat(card.getAttribute("data-price")) || 0;

            const matchesSearch =
                name.includes(searchText) ||
                description.includes(searchText);

            let matchesPrice = true;

            if (selectedPrice === "under500") {
                matchesPrice = price < 500;
            } else if (selectedPrice === "500to1000") {
                matchesPrice = price >= 500 && price <= 1000;
            } else if (selectedPrice === "above1000") {
                matchesPrice = price > 1000;
            }

            return matchesSearch && matchesPrice;
        });

        matchingCards.sort(function (a, b) {
            const priceA = parseFloat(a.getAttribute("data-price")) || 0;
            const priceB = parseFloat(b.getAttribute("data-price")) || 0;

            if (sortBy === "high") {
                return priceB - priceA;
            }

            if (sortBy === "name") {
                return (a.getAttribute("data-name") || "").localeCompare(
                    b.getAttribute("data-name") || ""
                );
            }

            return priceA - priceB;
        });

        matchingCards.forEach(function (card) {
            card.hidden = false;

            card.classList.toggle(
                "filter-highlight",
                filterIsActive
            );

            grid.appendChild(card);
        });

        cards.forEach(function (card) {
            if (!matchingCards.includes(card)) {
                card.hidden = true;
                card.classList.remove("filter-highlight");
            }
        });

        count.textContent =
            "Showing " + matchingCards.length + " of " + cards.length + " idols";

        noResults.hidden = matchingCards.length !== 0;
    }

    searchInput.addEventListener("input", updateCatalogue);
    priceFilter.addEventListener("change", updateCatalogue);
    sortSelect.addEventListener("change", updateCatalogue);

    updateCatalogue();
});
