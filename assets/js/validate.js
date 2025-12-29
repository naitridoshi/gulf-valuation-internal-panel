document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("valuationForm");
  if (!form) return;

  const inputs = form.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );
  const amountInWords = document.getElementById("amount_in_words");

  // Validate all fields on page load (for edit mode)
  function validateAllFields() {
    inputs.forEach((input) => validateField(input));
  }

  // Real-time validation
  inputs.forEach((input) => {
    input.addEventListener("input", function () {
      validateField(this);
    });
    input.addEventListener("blur", function () {
      validateField(this);
    });
    input.addEventListener("change", function () {
      validateField(this);
    });
  });

  // Valuation amount specific validation
  const valuationAmount = document.getElementById("valuation_amount");
  if (valuationAmount) {
    valuationAmount.addEventListener("input", function () {
      const value = parseFloat(this.value);
      if (value > 0) {
        this.classList.remove("invalid");
        this.classList.add("valid");
        amountInWords.textContent =
          "(Rials Omani " + numberToWords(value) + ")";
      } else {
        this.classList.remove("valid");
        this.classList.add("invalid");
        amountInWords.textContent = "";
      }
    });
  }

  // Form submission validation
  form.addEventListener("submit", function (e) {
    let hasError = false;
    inputs.forEach((input) => {
      if (!validateField(input)) {
        hasError = true;
      }
    });
    if (hasError) {
      e.preventDefault();
      alert("Please fill all required fields correctly.");
    }
  });

  // Validation function
  function validateField(input) {
    let isValid = false;
    if (input.type === "number") {
      isValid = input.value && parseFloat(input.value) > 0;
    } else if (input.tagName === "SELECT") {
      isValid = input.value && input.value !== "";
    } else {
      isValid = input.value.trim() !== "";
    }

    if (isValid) {
      input.classList.remove("invalid");
      input.classList.add("valid");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
    }
    return isValid;
  }

  // Run initial validation for pre-filled fields (edit mode)
  validateAllFields();

  // Number to words function
  function numberToWords(number) {
    const ones = [
      "Zero",
      "One",
      "Two",
      "Three",
      "Four",
      "Five",
      "Six",
      "Seven",
      "Eight",
      "Nine",
      "Ten",
      "Eleven",
      "Twelve",
      "Thirteen",
      "Fourteen",
      "Fifteen",
      "Sixteen",
      "Seventeen",
      "Eighteen",
      "Nineteen",
    ];
    const tens = [
      "",
      "",
      "Twenty",
      "Thirty",
      "Forty",
      "Fifty",
      "Sixty",
      "Seventy",
      "Eighty",
      "Ninety",
    ];
    const thousands = ["", "Thousand", "Million", "Billion"];

    if (number == 0) return "Zero";
    number = parseFloat(number);
    const whole = Math.floor(number);
    const decimal = Math.round((number - whole) * 1000);
    let words = [];

    if (whole > 0) {
      const chunks = String(whole)
        .padStart(Math.ceil(String(whole).length / 3) * 3, "0")
        .match(/.{1,3}/g)
        .reverse();
      chunks.forEach((chunk, i) => {
        chunk = parseInt(chunk);
        if (chunk == 0) return;
        let chunkWords = [];
        if (chunk >= 100) {
          chunkWords.push(ones[Math.floor(chunk / 100)] + " Hundred");
          chunk %= 100;
        }
        if (chunk >= 20) {
          chunkWords.push(tens[Math.floor(chunk / 10)]);
          chunk %= 10;
        }
        if (chunk > 0) {
          chunkWords.push(ones[chunk]);
        }
        if (chunkWords.length) {
          words.push(
            chunkWords.join(" ") + (thousands[i] ? " " + thousands[i] : "")
          );
        }
      });
      wholeWords = words.reverse().join(" ");
    } else {
      wholeWords = "Zero";
    }

    let decimalWords = "";
    if (decimal > 0) {
      decimalWords = " and " + numberToWords(decimal) + " Baizas";
    }

    return wholeWords + decimalWords + " Only";
  }
});
