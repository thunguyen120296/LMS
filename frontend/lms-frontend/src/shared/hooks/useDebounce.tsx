import { useState, useEffect } from 'react';
function useDebounce(value: string, delay: number){
    const [debouncedValue, setDebouncedValue] = useState<string | null>(null);
    useEffect(() => {
        const handler = setTimeout(() =>{
            setDebouncedValue(value)
        }, delay);
        clearInterval(handler);
    });
    return debouncedValue;
}

export default useDebounce;