export const debounce = function<T extends TimerHandler>(
    timer: ReturnType<typeof setTimeout>, fn:T, waitMs?: number) {
    return function() {
        if (timer) clearTimeout(timer)
        return setTimeout(fn, waitMs || 500)
    }()
}