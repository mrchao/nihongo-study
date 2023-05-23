export const shuffle = <T>(array: T[], length: number) => {
    const list = Array.from(array)
    for (let i = list.length - 1; i > 0; i--) {
        const r = Math.floor(Math.random() * (i + 1))
        const tmp = list[i]
        list[i] = list[r]
        list[r] = tmp
    }
    return list.slice(0, length)
}

export const random = <T>(array: T[]): T => {
    const r = Math.floor(Math.random() * (0 - array.length) + array.length);
    return array[r];
}