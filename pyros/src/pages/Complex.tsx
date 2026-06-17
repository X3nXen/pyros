import { Autocomplete, Box, TextField } from "@mui/material";
import { useEffect, useState } from "react";
import { ComplexFormData } from "../model/Complex.model";
import { StandingsShort } from "../model/Standings.model";
import Calls from "../controllers/Calls.control";

export default function Complex() {
  const [formData, setFormData] = useState<ComplexFormData>(
    {} as ComplexFormData,
  );
  const [mainStandings, setMainStandings] = useState<Array<StandingsShort>>([]);
  const [settlements, setSettlements] = useState<Array<{label: string, id: number}>>;

  useEffect(() => {
    async function callApi() {
      return await Calls.getMainStandings();
    }
    callApi()
      .then((result) => {
        if (result.success) {
          setMainStandings(result.payload);
        }
      })
      .catch((error) =>
        console.error("Probléma a backend hívással a useEffectben", error),
      );
  });
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
        <h1>Telephely rögzítése</h1>
        <TextField label="Cím" variant="standard" onChange={(e) => setFormData({...formData, address: e.target.value})}></TextField>
        <Autocomplete disablePortal options={} renderInput={(params) => <TextField {...params} label="Település"/>}/>
    </Box>
  );
}
