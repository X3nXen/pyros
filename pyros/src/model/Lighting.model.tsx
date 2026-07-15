export interface LightingFormData {
  id: string | null;
  zone: string;
  size: number;
  solution: string;
  dim: string;
  zoneUsage: string;
  regulation: string;
  naturalLight: string;
  emergency: boolean;
  standBy: boolean;
}

export interface LightingErrors {
    zone: string;
    size: string;
}

export const LightingSolutions: Array<string> = [
  "Normál izzólámpa – üvegburás/parabolatükrös",
  "Normál izzólámpa – opál burás",
  "Halogén izzólámpa – üvegburás/parabolatükrös",
  "Halogén izzólámpa – opál burás",
  "Fénycső – üvegburás/parabolatükrös",
  "Fénycső – opál burás",
  "Kompakt fénycső – üvegburás/parabolatükrös",
  "Kompakt fénycső – opál burás",
  "Higanylámpa – üvegburás/parabolatükrös",
  "Higanylámpa – opál burás",
  "Fémhalogén lámpa – üvegburás/parabolatükrös",
  "Fémhalogén lámpa – opál burás",
  "LED – bármely lámpatest-változat",
];

export const LightingDim: Array<string> = [
  "Nem dimmelhető világítási rendszer",
  "Dimmelhető halogén fényforrás",
  "Dimmelhető fénycső",
  "Dimmelhető LED",
];

export const ZoneUsage: Array<string> = [
  "Iroda / Irodaépület",
  "Oktatási intézmény / Oktatási épület",
  "Kórház / Kórház",
  "Hotel / Hotel",
  "Étterem / Étterem",
  "Sportcsarnok / Sportközpont",
  "Kereskedelmi egység / Kereskedelmi egység",
  "Üzem / Üzem",
  "Múzeum / Kereskedelmi egység üzemideje",
  "Könyvtár / Oktatási épület üzemideje",
  "Színház, auditórium / Étterem jellegű üzemidő",
  "Konferenciaterem, Kiállító terem / Kereskedelmi egység üzemideje",
];

export const LightingRegulation: Array<string> = [
  "Kézi be- és kikapcsolás",
  "Automatikus bekapcsolás/dimmelhető",
  "Automatikus be- és kikapcsolás",
  "Kézi bekapcsolás/dimmelhető",
  "Kézi bekapcsolás, automatikus kikapcsolás",
];

export const LightingNaturalLightRatio: Array<string> = [
  "80% fölött",
  "40% - 80% között",
  "40% alatt",
  "Nincs természetes világítás",
];
