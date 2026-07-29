import { useContext, useEffect } from 'react';
import { CustomizationContext } from '../context/CustomizationContex.jsx';

export default function CustomizerBridge() {
  const { setCustomization } = useContext(CustomizationContext);

  useEffect(() => {
    // Notify parent frame that the customizer is loaded and ready
    window.parent.postMessage({ type: 'CUSTOMIZER_READY' }, '*');

    const handleMessage = (event) => {
      if (event.data?.type === 'LOAD_CUSTOMIZATION') {
        const payload = event.data.payload;
        if (payload) {
          setCustomization(prev => ({
            ...prev,
            layerColor: payload.colors || prev.layerColor,
            layerSize: payload.sizes || prev.layerSize,
          }));
        }
      }
    };

    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [setCustomization]);

  return null;
}
