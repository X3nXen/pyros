import { Box } from "@mui/material";
import { useState } from "react";
import { ComplexFormData } from "../model/Complex.model";

export default function Complex(){
    const [formData, setFormData] = useState<ComplexFormData>({} as ComplexFormData);

    return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        gap: 2,
        width: "100%",
        maxWidth: 360,
        mt: 3,
      }}
    >

    </Box>);
}