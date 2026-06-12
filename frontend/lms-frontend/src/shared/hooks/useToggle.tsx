import {useState} from 'react';
function useToggle(){
    const [toggle, setToggle] = useState();
    const handleToggle = ()=>{
        setToggle((prev) => {!prev});
    }
    return {
        toggle,
        handleToggle
    }
}

export default useToggle;