import { Route, BrowserRouter, Routes } from 'react-router-dom'
import Home from './pages/Home'
import Layout from './components/Layout'
import Standings from './pages/Standings'
import Complex from './pages/Complex'
import Building from './pages/Building'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<Layout/>}>
          <Route path='/' element={<Home/>}/>
          <Route path='/standings' element={<Standings/>}/>
          <Route path='/complex' element={<Complex/>}/>
          <Route path="/building" element={<Building/>}/>
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
