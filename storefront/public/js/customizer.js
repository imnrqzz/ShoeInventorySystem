document.addEventListener('DOMContentLoaded', () => {
  const addCartButton = document.getElementById('customizerAddCart');
  if (!addCartButton) return;

  addCartButton.addEventListener('click', async () => {
    try {
      const response = await fetch('index.php?page=cart&customizer=1', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: 'Custom Sneaker',
          brand: 'SOLEHAUS',
          price: 4999,
        }),
      });

      const data = await response.json();
      if (data.ok) {
        alert('Custom sneaker added to cart!');
      } else {
        alert('Unable to add to cart. Please login and try again.');
      }
    } catch (error) {
      console.error(error);
      alert('An error occurred while adding the custom shoe to your cart.');
    }
  });
});
