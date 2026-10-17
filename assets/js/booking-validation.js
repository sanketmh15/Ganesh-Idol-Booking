
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("bookingForm");

    if (!form || !window.bookingConfig) return;

    const quantity = document.getElementById("quantity");
    const date = document.getElementById("bookingDate");
    const address = document.getElementById("deliveryAddress");
    const area = document.getElementById("deliveryArea");
    const city = document.getElementById("deliveryCity");
    const pin = document.getElementById("deliveryPincode");
    const mobile = document.getElementById("deliveryMobile");

    const total = document.getElementById("estimatedTotal");
    const message = document.getElementById("bookingMessage");

    function showError(id, text) {
        document.getElementById(id).textContent = text;
    }

    function updateTotal() {
        const qty = Number(quantity.value);
        const validQty =
            Number.isInteger(qty) &&
            qty >= 1 &&
            qty <= Math.min(20, window.bookingConfig.maxStock);

        if (validQty) {
            const amount =
                window.bookingConfig.price * qty +
                window.bookingConfig.deliveryCharge;

            total.textContent = "₹" + amount.toFixed(2);
        } else {
            total.textContent = "Enter a valid quantity";
        }
    }

    quantity.addEventListener("input", updateTotal);

    form.addEventListener("submit", function (event) {
        let errors = [];

        ["quantityError", "dateError", "addressError", "areaError",
         "cityError", "pinError", "mobileError"].forEach(function (id) {
            showError(id, "");
        });

        const qty = Number(quantity.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (
            !Number.isInteger(qty) ||
            qty < 1 ||
            qty > 20 ||
            qty > window.bookingConfig.maxStock
        ) {
            showError(
                "quantityError",
                "Choose a quantity within available stock (maximum 20)."
            );
            errors.push("quantity");
        }

        if (!date.value || date.value < new Date().toLocaleDateString("en-CA")) {
            showError("dateError", "Choose today or a future delivery date.");
            errors.push("date");
        }

        if (address.value.trim().length < 5) {
            showError("addressError", "Enter a complete delivery address.");
            errors.push("address");
        }

        if (area.value.trim().length < 2) {
            showError("areaError", "Enter your area or locality.");
            errors.push("area");
        }

        if (city.value.trim().length < 2) {
            showError("cityError", "Enter your city.");
            errors.push("city");
        }

        if (!/^[0-9]{6}$/.test(pin.value.trim())) {
            showError("pinError", "PIN code must contain exactly 6 digits.");
            errors.push("pin");
        }

        if (!/^[0-9+()\-]{10,15}$/.test(mobile.value.trim())) {
            showError("mobileError", "Enter a valid mobile number.");
            errors.push("mobile");
        }

        if (errors.length > 0) {
            event.preventDefault();
            message.hidden = false;
            message.textContent =
                "Please correct the highlighted fields before booking.";

            const firstInvalid = form.querySelector(
                "#" + ({
                    quantity: "quantity",
                    date: "bookingDate",
                    address: "deliveryAddress",
                    area: "deliveryArea",
                    city: "deliveryCity",
                    pin: "deliveryPincode",
                    mobile: "deliveryMobile"
                }[errors[0]])
            );

            if (firstInvalid) firstInvalid.focus();
        } else {
            message.hidden = true;
        }
    });

    updateTotal();
});
