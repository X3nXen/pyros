import { Route, BrowserRouter, Routes } from 'react-router-dom'
import Home from './pages/Home'
import Layout from './components/Layout'
import Standings from './pages/Standings'
import Complex from './pages/Complex'
import Building from './pages/Building'
import System from './pages/System'
import HeatingSystem from './pages/HeatingSystem'
import LightingSystem from './pages/LightingSystem'
import VentilationSystem from './pages/VentilationSystem'
import Vehicles from './pages/Vehicles'
import Product from './pages/Product'
import Technology from './pages/Technology'
import Compressed from './pages/Compressed'
import Steam from './pages/Steam'
import Cooling from './pages/Cooling'
import Other from './pages/Other'
import Variables from './pages/Variables'
import Create from './pages/Create'

function App() {
    return (
        <BrowserRouter>
            <Routes>
                <Route element={<Layout />}>
                    <Route path="/" element={<Home />} />
                    <Route path="/standings" element={<Standings />} />
                    <Route path="/complex" element={<Complex />} />
                    <Route path="/building" element={<Building />} />
                    <Route path="/system" element={<System />} />
                    <Route path="/system/heating" element={<HeatingSystem />} />
                    <Route
                        path="/system/lighting"
                        element={<LightingSystem />}
                    />
                    <Route
                        path="/system/ventilation"
                        element={<VentilationSystem />}
                    />
                    <Route path="/vehicles" element={<Vehicles />} />
                    <Route path="/product" element={<Product />} />
                    <Route path="/technology" element={<Technology />} />
                    <Route
                        path="/technology/compressed"
                        element={<Compressed />}
                    />
                    <Route path="/technology/steam" element={<Steam />} />
                    <Route path="/technology/cooling" element={<Cooling />} />
                    <Route path="/technology/other" element={<Other />} />
                    <Route path="/variables" element={<Variables />} />
                    <Route path="/create" element={<Create />} />
                </Route>
            </Routes>
        </BrowserRouter>
    )
}

export default App
