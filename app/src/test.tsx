import React, { useState } from 'react'
import ReactDOM from 'react-dom/client'

ReactDOM.createRoot(document.getElementById('root')!).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>,
)

function App() {
    const [num, setNum] = useState(0);

    return (
        <div>
            {num} <br />
            <button onClick={() => { setNum(v => v + 1); }}>Wowwewewe</button>
        </div>
    )
}