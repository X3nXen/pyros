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

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<Layout/>}>
          <Route path='/' element={<Home/>}/>
          <Route path='/standings' element={<Standings/>}/>
          <Route path='/complex' element={<Complex/>}/>
          <Route path="/building" element={<Building/>}/>
          <Route path="/system" element={<System/>}/>
          <Route path="/system/heating" element={<HeatingSystem />} />
          <Route path="/system/lighting" element={<LightingSystem />} />
          <Route path="/system/ventilation" element={<VentilationSystem />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
