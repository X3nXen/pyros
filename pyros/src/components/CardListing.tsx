import AddIcon from "@mui/icons-material/Add";
import { Box, Card as MuiCard, CardActionArea, Typography } from "@mui/material";

interface CardProps {
  name: string;
  isActive: boolean;
  onClick: () => void;
}

export function Card({ name, isActive, onClick }: CardProps) {
  return (
    <MuiCard
      variant="outlined"
      sx={{
        borderColor: isActive ? "primary.main" : "divider",
        borderWidth: isActive ? 2 : 1,
        boxShadow: isActive ? 2 : 0,
        backgroundColor: isActive ? "rgba(25, 118, 210, 0.04)" : "background.paper",
        transition: "all 0.2s ease",
      }}
    >
      <CardActionArea onClick={onClick} sx={{ p: 2, height: "100%" }}>
        <Typography
          variant="subtitle2"
          sx={{
            fontWeight: isActive ? "bold" : "regular",
            color: isActive ? "primary.main" : "text.primary",
            whiteSpace: "nowrap",
            overflow: "hidden",
            textOverflow: "ellipsis",
          }}
        >
          {name || "Névtelen elem"}
        </Typography>
      </CardActionArea>
    </MuiCard>
  );
}

interface CardListingProps<T> {
  items: T[];
  activeIndex: number | null;
  onSelect: (index: number) => void;
  onAdd: () => void;
  getName: (item: T) => string; // Egy függvény, ami megmondja, mi az elem "neve" a kártyán
}

export default function CardListing<T>({
  items,
  activeIndex,
  onSelect,
  onAdd,
  getName,
}: CardListingProps<T>) {
  return (
    <Box sx={{ width: "100%", display: "flex", flexDirection: "column", gap: 1.5 }}>
      <Box
        sx={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fill, minmax(140px, 1fr))",
          gap: 1.5,
          width: "100%",
        }}
      >
        {items.map((item, index) => (
          <Card
            key={index}
            name={getName(item)}
            isActive={activeIndex === index}
            onClick={() => onSelect(index)}
          />
        ))}

        <MuiCard
          variant="outlined"
          sx={{
            borderStyle: "dashed",
            borderColor: "primary.main",
            backgroundColor: "transparent",
          }}
        >
          <CardActionArea
            onClick={onAdd}
            sx={{
              p: 2,
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              height: "100%",
              minHeight: 52,
              color: "primary.main",
            }}
          >
            <AddIcon />
          </CardActionArea>
        </MuiCard>
      </Box>
    </Box>
  );
}