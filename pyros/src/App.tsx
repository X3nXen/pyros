import { Route, BrowserRouter, Routes } from 'react-router-dom'
import Home from './pages/Home'
import Layout from './components/Layout'
import Standings from './pages/Standings'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<Layout/>}>
          <Route path='/' element={<Home/>}/>
          <Route path='/standings' element={<Standings/>}/>
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
