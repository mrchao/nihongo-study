export const levelOptions = new Array(6).fill(1).map((v, i) => "L" + ++i)
export const lessonOptions = new Array(25)
    .fill(1)
    .map((v,i) => {
    return {
        label: i === 0 ? '请选择' : '第'+ i + '课',
        value: i
    }
})