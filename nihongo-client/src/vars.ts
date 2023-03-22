export const levelOptions = new Array(6)
    .fill(1)
    .map((v, i) => {
        ++i
        return {
            label: 'L' + i,
            value: i
        }
    })
export const lessonOptions = new Array(24)
    .fill(1)
    .map((v,i) => {
        ++i
    return {
        label: '第'+ (i < 10 ? '0' + i : i) + '课',
        value: i
    }
})

export const shuffle = (arr:Array<number>) => {
    let newArr = arr.map(item => ({val:item, ram:Math.random()}))
    newArr.sort((a,b) => a.ram - b.ram)
    arr.splice(0, arr.length, ...newArr.map(i => i.val))
    return arr
}