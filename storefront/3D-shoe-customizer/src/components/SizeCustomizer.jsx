import { useContext } from "react";
import { CustomizationContext } from "../context/CustomizationContex.jsx";
import { Select, Text, Stack } from "@chakra-ui/react";

export default function SizeCustomizer() {
  const { customization, setCustomization } = useContext(CustomizationContext);

  const handleSizeChange = (event) => {
    const value = event.target.value;
    setCustomization((prevState) => ({
      ...prevState,
      shoeSize: value
    }));
  };

  return (
    <Stack spacing={3}>
      <Text fontSize="sm">Select US Shoe Size:</Text>
      <Select 
        placeholder="Select size" 
        value={customization.shoeSize || ""} 
        onChange={handleSizeChange}
        color="white"
        bg="rgba(0,0,0,0.5)"
        borderColor="var(--border)"
        _hover={{ borderColor: "var(--accent)" }}
      >
        <option value="5" style={{ background: '#111' }}>US 5</option>
        <option value="5.5" style={{ background: '#111' }}>US 5.5</option>
        <option value="6" style={{ background: '#111' }}>US 6</option>
        <option value="6.5" style={{ background: '#111' }}>US 6.5</option>
        <option value="7" style={{ background: '#111' }}>US 7</option>
        <option value="7.5" style={{ background: '#111' }}>US 7.5</option>
        <option value="8" style={{ background: '#111' }}>US 8</option>
        <option value="8.5" style={{ background: '#111' }}>US 8.5</option>
        <option value="9" style={{ background: '#111' }}>US 9</option>
        <option value="9.5" style={{ background: '#111' }}>US 9.5</option>
        <option value="10" style={{ background: '#111' }}>US 10</option>
        <option value="10.5" style={{ background: '#111' }}>US 10.5</option>
        <option value="11" style={{ background: '#111' }}>US 11</option>
        <option value="12" style={{ background: '#111' }}>US 12</option>
      </Select>
    </Stack>
  );
}
