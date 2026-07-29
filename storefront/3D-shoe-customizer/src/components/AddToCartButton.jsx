import { useContext, useState, useEffect } from 'react';
import { CustomizationContext } from '../context/CustomizationContex.jsx';

export default function AddToCartButton() {
  const { customization } = useContext(CustomizationContext);
  const [status, setStatus] = useState('idle'); // idle | sending | done | error

  const handleAddToCart = () => {
    setStatus('sending');
    const params = new URLSearchParams(window.location.search);
    const productId = params.get('productId') || 1;
    const editCartItemId = params.get('editCartItemId');

    window.parent.postMessage({
      type: 'ADD_TO_CART',
      payload: {
        productId,
        editCartItemId,
        layerColor: customization.layerColor,
        layerSize: customization.layerSize,
        shoeSize: customization.shoeSize || 9,
      }
    }, '*'); // tighten to your storefront origin in production
  };

  useEffect(() => {
    const handleMessage = (event) => {
      if (event.data?.type === 'ADD_TO_CART_SUCCESS') setStatus('done');
      if (event.data?.type === 'ADD_TO_CART_ERROR') setStatus('error');
    };
    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, []);

  return (
    <button
      onClick={handleAddToCart}
      disabled={status === 'sending'}
      style={{
        position: 'fixed', bottom: '20px', right: '20px', zIndex: 1000,
        padding: '12px 24px', background: '#111', color: '#fff',
        border: 'none', borderRadius: '8px', cursor: 'pointer', fontWeight: 600,
      }}
    >
      {status === 'done' ? 'Added ✓' : status === 'error' ? 'Retry' : 'Add to Cart'}
    </button>
  );
}
