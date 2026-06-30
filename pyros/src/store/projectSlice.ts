import Calls from "../controllers/Calls.control";
import type { BuildingShort } from "../model/Building.model";
import type { ComplexShortData } from "../model/Complex.model";
import type { ClickupTaskShort } from "../model/LoginData.model";
import type { StandingsShort } from "../model/Standings.model";
import {
  createSlice,
  createAsyncThunk,
  type PayloadAction,
} from "@reduxjs/toolkit";

interface ProjectState {
  currentTaskId: string | null;
  clickupTasks: Array<ClickupTaskShort>;
  mainStandings: Array<StandingsShort>;
  subStandings: Array<StandingsShort>;
  complexes: Array<ComplexShortData>;
  buildings: Array<BuildingShort>;
  loading: boolean;
  error: string | null;
}

const initialState: ProjectState = {
  currentTaskId: null,
  clickupTasks: [],
  mainStandings: [],
  subStandings: [],
  complexes: [],
  buildings: [],
  loading: false,
  error: null,
};

export const fetchClickupData = createAsyncThunk(
  "project/fetchClickupTasks",
  async (_, { rejectWithValue }) => {
    try{
      const response = await Calls.getClickupTasks();
      return response.success ? response.payload : [];
    } catch(error) {
      return rejectWithValue(
        error || "Hiba történt a Clickuppal való lekérdezésben",
      );
    }
  }
);

export const fetchProjectData = createAsyncThunk(
  "project/fetchData",
  async (_, { rejectWithValue }) => {
    try {
      const [mainRes, subRes, complexRes, buildingRes] = await Promise.all([
        Calls.getMainStandings(),
        Calls.getSubStandings(),
        Calls.getComplexes(),
        Calls.getBuildings()
      ]);
      return {
        mainStandings: mainRes.success ? mainRes.payload : [],
        subStandings: subRes.success ? subRes.payload : [],
        complexes: complexRes.success ? complexRes.payload : [],
        buildings: buildingRes.success ? buildingRes.payload : []
      };
    } catch (error) {
      return rejectWithValue(
        error || "Hiba történt az adatok lekérésekor.",
      );
    }
  },
);

const projectSlice = createSlice({
  name: "project",
  initialState: initialState,
  reducers: {
    changeProject: (state: ProjectState, action: PayloadAction<string>) => {
      state.currentTaskId = action.payload;
      state.mainStandings = [];
      state.subStandings = [];
      state.complexes = [];
      state.buildings = []
    },
    addComplexLocally: (state: ProjectState, action: PayloadAction<ComplexShortData>) => {
      state.complexes.push(action.payload);
    },
    addMainStandingLocally: (state: ProjectState, action: PayloadAction<StandingsShort>) => {
      state.mainStandings.push(action.payload);
    },
    addSubStandingLocally: (state: ProjectState, action: PayloadAction<StandingsShort>) => {
      state.subStandings.push(action.payload);
    },
    addBuildingLocally: (state: ProjectState, action: PayloadAction<BuildingShort>) => {
      state.buildings.push(action.payload);
    }
  },
  extraReducers: (builder) => {
    builder
    .addCase(fetchClickupData.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchClickupData.fulfilled, (state, action) => {
        state.loading = false;
        state.clickupTasks = action.payload;
      })
      .addCase(fetchClickupData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      .addCase(fetchProjectData.pending, (state: ProjectState) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProjectData.fulfilled, (state: ProjectState, action) => {
        state.loading = false;
        state.mainStandings = action.payload.mainStandings;
        state.subStandings = action.payload.subStandings;
        state.complexes = action.payload.complexes;
        state.buildings = action.payload.buildings;
      })
      .addCase(fetchProjectData.rejected, (state: ProjectState, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
  },
});

export const {
  changeProject,
  addComplexLocally,
  addMainStandingLocally,
  addSubStandingLocally,
  addBuildingLocally
} = projectSlice.actions;

export default projectSlice.reducer;
