var Rn = Object.defineProperty, Bn = Object.defineProperties;
var jn = Object.getOwnPropertyDescriptors;
var we = Object.getOwnPropertySymbols;
var yt = Object.prototype.hasOwnProperty, vt = Object.prototype.propertyIsEnumerable;
var wt = (r, V, N) => V in r ? Rn(r, V, {enumerable: !0, configurable: !0, writable: !0, value: N}) : r[V] = N,
    Y = (r, V) => {
        for (var N in V || (V = {})) yt.call(V, N) && wt(r, N, V[N]);
        if (we) for (var N of we(V)) vt.call(V, N) && wt(r, N, V[N]);
        return r
    }, A = (r, V) => Bn(r, jn(V));
var Dt = (r, V) => {
    var N = {};
    for (var E in r) yt.call(r, E) && V.indexOf(E) < 0 && (N[E] = r[E]);
    if (r != null && we) for (var E of we(r)) V.indexOf(E) < 0 && vt.call(r, E) && (N[E] = r[E]);
    return N
};
(function (r, V) {
    typeof exports == "object" && typeof module != "undefined" ? module.exports = V(require("vue")) : typeof define == "function" && define.amd ? define(["vue"], V) : (r = typeof globalThis != "undefined" ? globalThis : r || self, r.DatePicker = V(r.BX.Vue3))
})(this, function (r) {
    "use strict";

    function V(t) {
        return t instanceof Date || Object.prototype.toString.call(t) === "[object Date]"
    }

    function N(t) {
        return V(t) ? new Date(t.getTime()) : t == null ? new Date(NaN) : new Date(t)
    }

    function E(t) {
        return V(t) && !isNaN(t.getTime())
    }

    function We(t) {
        var e = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 0;
        if (!(e >= 0 && e <= 6)) throw new RangeError("weekStartsOn must be between 0 and 6");
        var n = N(t), a = n.getDay(), o = (a + 7 - e) % 7;
        return n.setDate(n.getDate() - o), n.setHours(0, 0, 0, 0), n
    }

    function _e(t) {
        var e = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {}, n = e.firstDayOfWeek,
            a = n === void 0 ? 0 : n, o = e.firstWeekContainsDate, l = o === void 0 ? 1 : o;
        if (!(l >= 1 && l <= 7)) throw new RangeError("firstWeekContainsDate must be between 1 and 7");
        for (var i = N(t), s = i.getFullYear(), u = new Date(0), c = s + 1; c >= s - 1 && (u.setFullYear(c, 0, l), u.setHours(0, 0, 0, 0), u = We(u, a), !(i.getTime() >= u.getTime())); c--) ;
        return u
    }

    function De(t) {
        var e = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {}, n = e.firstDayOfWeek,
            a = n === void 0 ? 0 : n, o = e.firstWeekContainsDate, l = o === void 0 ? 1 : o, i = N(t), s = We(i, a),
            u = _e(i, {firstDayOfWeek: a, firstWeekContainsDate: l}), c = s.getTime() - u.getTime();
        return Math.round(c / (7 * 24 * 3600 * 1e3)) + 1
    }

    var be = {
        months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        weekdays: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        weekdaysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        weekdaysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
        firstDayOfWeek: 0,
        firstWeekContainsDate: 1
    }, bt = /\[([^\]]+)]|YYYY|YY?|M{1,4}|D{1,2}|d{1,4}|H{1,2}|h{1,2}|m{1,2}|s{1,2}|Z{1,2}|S{1,3}|w{1,2}|x|X|a|A/g;

    function P(t) {
        for (var e = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 2, n = "".concat(Math.abs(t)), a = t < 0 ? "-" : ""; n.length < e;) n = "0".concat(n);
        return a + n
    }

    function Ie(t) {
        return Math.round(t.getTimezoneOffset() / 15) * 15
    }

    function Le(t) {
        var e = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : "", n = t > 0 ? "-" : "+",
            a = Math.abs(t), o = Math.floor(a / 60), l = a % 60;
        return n + P(o, 2) + e + P(l, 2)
    }

    var Ue = function (e, n, a) {
        var o = e < 12 ? "AM" : "PM";
        return a ? o.toLocaleLowerCase() : o
    }, ae = {
        Y: function (e) {
            var n = e.getFullYear();
            return n <= 9999 ? "".concat(n) : "+".concat(n)
        }, YY: function (e) {
            return P(e.getFullYear(), 4).substr(2)
        }, YYYY: function (e) {
            return P(e.getFullYear(), 4)
        }, M: function (e) {
            return e.getMonth() + 1
        }, MM: function (e) {
            return P(e.getMonth() + 1, 2)
        }, MMM: function (e, n) {
            return n.monthsShort[e.getMonth()]
        }, MMMM: function (e, n) {
            return n.months[e.getMonth()]
        }, D: function (e) {
            return e.getDate()
        }, DD: function (e) {
            return P(e.getDate(), 2)
        }, H: function (e) {
            return e.getHours()
        }, HH: function (e) {
            return P(e.getHours(), 2)
        }, h: function (e) {
            var n = e.getHours();
            return n === 0 ? 12 : n > 12 ? n % 12 : n
        }, hh: function () {
            var e = ae.h.apply(ae, arguments);
            return P(e, 2)
        }, m: function (e) {
            return e.getMinutes()
        }, mm: function (e) {
            return P(e.getMinutes(), 2)
        }, s: function (e) {
            return e.getSeconds()
        }, ss: function (e) {
            return P(e.getSeconds(), 2)
        }, S: function (e) {
            return Math.floor(e.getMilliseconds() / 100)
        }, SS: function (e) {
            return P(Math.floor(e.getMilliseconds() / 10), 2)
        }, SSS: function (e) {
            return P(e.getMilliseconds(), 3)
        }, d: function (e) {
            return e.getDay()
        }, dd: function (e, n) {
            return n.weekdaysMin[e.getDay()]
        }, ddd: function (e, n) {
            return n.weekdaysShort[e.getDay()]
        }, dddd: function (e, n) {
            return n.weekdays[e.getDay()]
        }, A: function (e, n) {
            var a = n.meridiem || Ue;
            return a(e.getHours(), e.getMinutes(), !1)
        }, a: function (e, n) {
            var a = n.meridiem || Ue;
            return a(e.getHours(), e.getMinutes(), !0)
        }, Z: function (e) {
            return Le(Ie(e), ":")
        }, ZZ: function (e) {
            return Le(Ie(e))
        }, X: function (e) {
            return Math.floor(e.getTime() / 1e3)
        }, x: function (e) {
            return e.getTime()
        }, w: function (e, n) {
            return De(e, {firstDayOfWeek: n.firstDayOfWeek, firstWeekContainsDate: n.firstWeekContainsDate})
        }, ww: function (e, n) {
            return P(ae.w(e, n), 2)
        }
    };

    function Ce(t, e) {
        var n = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {},
            a = e ? String(e) : "YYYY-MM-DDTHH:mm:ss.SSSZ", o = N(t);
        if (!E(o)) return "Invalid Date";
        var l = n.locale || be;
        return a.replace(bt, function (i, s) {
            return s || (typeof ae[i] == "function" ? "".concat(ae[i](o, l)) : i)
        })
    }

    function Re(t) {
        return Tt(t) || Mt(t) || Ct()
    }

    function Ct() {
        throw new TypeError("Invalid attempt to spread non-iterable instance")
    }

    function Mt(t) {
        if (Symbol.iterator in Object(t) || Object.prototype.toString.call(t) === "[object Arguments]") return Array.from(t)
    }

    function Tt(t) {
        if (Array.isArray(t)) {
            for (var e = 0, n = new Array(t.length); e < t.length; e++) n[e] = t[e];
            return n
        }
    }

    function Be(t, e) {
        var n = Object.keys(t);
        if (Object.getOwnPropertySymbols) {
            var a = Object.getOwnPropertySymbols(t);
            e && (a = a.filter(function (o) {
                return Object.getOwnPropertyDescriptor(t, o).enumerable
            })), n.push.apply(n, a)
        }
        return n
    }

    function Vt(t) {
        for (var e = 1; e < arguments.length; e++) {
            var n = arguments[e] != null ? arguments[e] : {};
            e % 2 ? Be(n, !0).forEach(function (a) {
                R(t, a, n[a])
            }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(t, Object.getOwnPropertyDescriptors(n)) : Be(n).forEach(function (a) {
                Object.defineProperty(t, a, Object.getOwnPropertyDescriptor(n, a))
            })
        }
        return t
    }

    function kt(t, e) {
        return $t(t) || Yt(t, e) || Nt()
    }

    function Nt() {
        throw new TypeError("Invalid attempt to destructure non-iterable instance")
    }

    function Yt(t, e) {
        if (Symbol.iterator in Object(t) || Object.prototype.toString.call(t) === "[object Arguments]") {
            var n = [], a = !0, o = !1, l = void 0;
            try {
                for (var i = t[Symbol.iterator](), s; !(a = (s = i.next()).done) && (n.push(s.value), !(e && n.length === e)); a = !0) ;
            } catch (u) {
                o = !0, l = u
            } finally {
                try {
                    !a && i.return != null && i.return()
                } finally {
                    if (o) throw l
                }
            }
            return n
        }
    }

    function $t(t) {
        if (Array.isArray(t)) return t
    }

    function R(t, e, n) {
        return e in t ? Object.defineProperty(t, e, {
            value: n,
            enumerable: !0,
            configurable: !0,
            writable: !0
        }) : t[e] = n, t
    }

    var St = /(\[[^\[]*\])|(MM?M?M?|Do|DD?|ddd?d?|w[o|w]?|YYYY|YY|a|A|hh?|HH?|mm?|ss?|S{1,3}|x|X|ZZ?|.)/g, je = /\d/,
        B = /\d\d/, xt = /\d{3}/, Pt = /\d{4}/, Q = /\d\d?/, Ot = /[+-]\d\d:?\d\d/, ze = /[+-]?\d+/,
        At = /[+-]?\d+(\.\d{1,3})?/, Me = "year", se = "month", Ze = "day", Ke = "hour", Xe = "minute", Je = "second",
        Te = "millisecond", qe = {}, M = function (e, n, a) {
            var o = Array.isArray(e) ? e : [e], l;
            typeof a == "string" ? l = function (s) {
                var u = parseInt(s, 10);
                return R({}, a, u)
            } : l = a, o.forEach(function (i) {
                qe[i] = [n, l]
            })
        }, Et = function (e) {
            return e.replace(/[|\\{}()[\]^$+*?.]/g, "\\$&")
        }, re = function (e) {
            return function (n) {
                var a = n[e];
                if (!Array.isArray(a)) throw new Error("Locale[".concat(e, "] need an array"));
                return new RegExp(a.map(Et).join("|"))
            }
        }, oe = function (e, n) {
            return function (a, o) {
                var l = o[e];
                if (!Array.isArray(l)) throw new Error("Locale[".concat(e, "] need an array"));
                var i = l.indexOf(a);
                if (i < 0) throw new Error("Invalid Word");
                return R({}, n, i)
            }
        };
    M("Y", ze, Me), M("YY", B, function (t) {
        var e = new Date().getFullYear(), n = Math.floor(e / 100), a = parseInt(t, 10);
        return a = (a > 68 ? n - 1 : n) * 100 + a, R({}, Me, a)
    }), M("YYYY", Pt, Me), M("M", Q, function (t) {
        return R({}, se, parseInt(t, 10) - 1)
    }), M("MM", B, function (t) {
        return R({}, se, parseInt(t, 10) - 1)
    }), M("MMM", re("monthsShort"), oe("monthsShort", se)), M("MMMM", re("months"), oe("months", se)), M("D", Q, Ze), M("DD", B, Ze), M(["H", "h"], Q, Ke), M(["HH", "hh"], B, Ke), M("m", Q, Xe), M("mm", B, Xe), M("s", Q, Je), M("ss", B, Je), M("S", je, function (t) {
        return R({}, Te, parseInt(t, 10) * 100)
    }), M("SS", B, function (t) {
        return R({}, Te, parseInt(t, 10) * 10)
    }), M("SSS", xt, Te);

    function Ht(t) {
        return t.meridiemParse || /[ap]\.?m?\.?/i
    }

    function Ft(t) {
        return "".concat(t).toLowerCase().charAt(0) === "p"
    }

    M(["A", "a"], Ht, function (t, e) {
        var n = typeof e.isPM == "function" ? e.isPM(t) : Ft(t);
        return {isPM: n}
    });

    function Wt(t) {
        var e = t.match(/([+-]|\d\d)/g) || ["-", "0", "0"], n = kt(e, 3), a = n[0], o = n[1], l = n[2],
            i = parseInt(o, 10) * 60 + parseInt(l, 10);
        return i === 0 ? 0 : a === "+" ? -i : +i
    }

    M(["Z", "ZZ"], Ot, function (t) {
        return {offset: Wt(t)}
    }), M("x", ze, function (t) {
        return {date: new Date(parseInt(t, 10))}
    }), M("X", At, function (t) {
        return {date: new Date(parseFloat(t) * 1e3)}
    }), M("d", je, "weekday"), M("dd", re("weekdaysMin"), oe("weekdaysMin", "weekday")), M("ddd", re("weekdaysShort"), oe("weekdaysShort", "weekday")), M("dddd", re("weekdays"), oe("weekdays", "weekday")), M("w", Q, "week"), M("ww", B, "week");

    function _t(t, e) {
        if (t !== void 0 && e !== void 0) {
            if (e) {
                if (t < 12) return t + 12
            } else if (t === 12) return 0
        }
        return t
    }

    function It(t) {
        for (var e = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : new Date, n = [0, 0, 1, 0, 0, 0, 0], a = [e.getFullYear(), e.getMonth(), e.getDate(), e.getHours(), e.getMinutes(), e.getSeconds(), e.getMilliseconds()], o = !0, l = 0; l < 7; l++) t[l] === void 0 ? n[l] = o ? a[l] : n[l] : (n[l] = t[l], o = !1);
        return n
    }

    function Lt(t, e, n, a, o, l, i) {
        var s;
        return t < 100 && t >= 0 ? (s = new Date(t + 400, e, n, a, o, l, i), isFinite(s.getFullYear()) && s.setFullYear(t)) : s = new Date(t, e, n, a, o, l, i), s
    }

    function Ut() {
        for (var t, e = arguments.length, n = new Array(e), a = 0; a < e; a++) n[a] = arguments[a];
        var o = n[0];
        return o < 100 && o >= 0 ? (n[0] += 400, t = new Date(Date.UTC.apply(Date, n)), isFinite(t.getUTCFullYear()) && t.setUTCFullYear(o)) : t = new Date(Date.UTC.apply(Date, n)), t
    }

    function Rt(t, e, n) {
        var a = e.match(St);
        if (!a) throw new Error;
        for (var o = a.length, l = {}, i = 0; i < o; i += 1) {
            var s = a[i], u = qe[s];
            if (u) {
                var f = typeof u[0] == "function" ? u[0](n) : u[0], p = u[1], D = (f.exec(t) || [])[0], h = p(D, n);
                l = Vt({}, l, {}, h), t = t.replace(D, "")
            } else {
                var c = s.replace(/^\[|\]$/g, "");
                if (t.indexOf(c) === 0) t = t.substr(c.length); else throw new Error("not match")
            }
        }
        return l
    }

    function Bt(t, e) {
        var n = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
        try {
            var a = n.locale, o = a === void 0 ? be : a, l = n.backupDate, i = l === void 0 ? new Date : l,
                s = Rt(t, e, o), u = s.year, c = s.month, f = s.day, p = s.hour, D = s.minute, h = s.second,
                w = s.millisecond, v = s.isPM, m = s.date, d = s.offset, y = s.weekday, C = s.week;
            if (m) return m;
            var T = [u, c, f, p, D, h, w];
            if (T[3] = _t(T[3], v), C !== void 0 && c === void 0 && f === void 0) {
                var $ = _e(u === void 0 ? i : new Date(u, 3), {
                    firstDayOfWeek: o.firstDayOfWeek,
                    firstWeekContainsDate: o.firstWeekContainsDate
                });
                return new Date($.getTime() + (C - 1) * 7 * 24 * 3600 * 1e3)
            }
            var g, b = It(T, i);
            return d !== void 0 ? (b[6] += d * 60 * 1e3, g = Ut.apply(void 0, Re(b))) : g = Lt.apply(void 0, Re(b)), y !== void 0 && g.getDay() !== y ? new Date(NaN) : g
        } catch (O) {
            return new Date(NaN)
        }
    }

    const jt = {formatLocale: be, yearFormat: "YYYY", monthFormat: "MMM", monthBeforeYear: !0};
    let le = "en";
    const ee = {};
    ee[le] = jt;

    function Ge(t, e, n = !1) {
        if (typeof t != "string") return ee[le];
        let a = le;
        return ee[t] && (a = t), e && (ee[t] = e, a = t), n || (le = a), ee[t] || ee[le]
    }

    function Ve(t) {
        return Ge(t, void 0, !0)
    }

    function ke(t, e) {
        if (!Array.isArray(t)) return [];
        const n = [], a = t.length;
        let o = 0;
        for (e = e || a; o < a;) n.push(t.slice(o, o += e));
        return n
    }

    function Qe(t) {
        return Array.isArray(t) ? t[t.length - 1] : void 0
    }

    function j(t) {
        return Object.prototype.toString.call(t) === "[object Object]"
    }

    function F(t, e) {
        const n = {};
        return j(t) && (Array.isArray(e) || (e = [e]), e.forEach(a => {
            Object.prototype.hasOwnProperty.call(t, a) && (n[a] = t[a])
        })), n
    }

    function et(t, e) {
        if (!j(t)) return {};
        let n = t;
        return j(e) && Object.keys(e).forEach(a => {
            let o = e[a];
            const l = t[a];
            j(o) && j(l) && (o = et(l, o)), n = A(Y({}, n), {[a]: o})
        }), n
    }

    function Ne(t) {
        const e = parseInt(String(t), 10);
        return e < 10 ? `0${e}` : `${e}`
    }

    function zt(t) {
        const e = /-(\w)/g;
        return t.replace(e, (n, a) => a ? a.toUpperCase() : "")
    }

    const tt = "datepicker_locale", nt = "datepicker_prefixClass", at = "datepicker_getWeek";

    function Ye() {
        return r.inject(tt, r.shallowRef(Ve()))
    }

    function Zt(t) {
        const e = r.computed(() => j(t.value) ? et(Ve(), t.value) : Ve(t.value));
        return r.provide(tt, e), e
    }

    function Kt(t) {
        r.provide(nt, t)
    }

    function S() {
        return r.inject(nt, "mx")
    }

    function Xt(t) {
        r.provide(at, t)
    }

    function Jt() {
        return r.inject(at, De)
    }

    function qt(t) {
        const e = t.style.display, n = t.style.visibility;
        t.style.display = "block", t.style.visibility = "hidden";
        const a = window.getComputedStyle(t),
            o = t.offsetWidth + parseInt(a.marginLeft, 10) + parseInt(a.marginRight, 10),
            l = t.offsetHeight + parseInt(a.marginTop, 10) + parseInt(a.marginBottom, 10);
        return t.style.display = e, t.style.visibility = n, {width: o, height: l}
    }

    function Gt(t, e, n, a) {
        let o = 0, l = 0, i = 0, s = 0;
        const u = t.getBoundingClientRect(), c = document.documentElement.clientWidth,
            f = document.documentElement.clientHeight;
        return a && (i = window.pageXOffset + u.left, s = window.pageYOffset + u.top), c - u.left < e && u.right < e ? o = i - u.left + 1 : u.left + u.width / 2 <= c / 2 ? o = i : o = i + u.width - e, u.top <= n && f - u.bottom <= n ? l = s + f - u.top - n : u.top + u.height / 2 <= f / 2 ? l = s + u.height : l = s - n, {
            left: `${o}px`,
            top: `${l}px`
        }
    }

    function $e(t, e = document.body) {
        if (!t || t === e) return null;
        const n = (l, i) => getComputedStyle(l, null).getPropertyValue(i);
        return /(auto|scroll)/.test(n(t, "overflow") + n(t, "overflow-y") + n(t, "overflow-x")) ? t : $e(t.parentElement, e)
    }

    let ce;

    function Qt() {
        if (typeof window == "undefined") return 0;
        if (ce !== void 0) return ce;
        const t = document.createElement("div");
        t.style.visibility = "hidden", t.style.overflow = "scroll", t.style.width = "100px", t.style.position = "absolute", t.style.top = "-9999px", document.body.appendChild(t);
        const e = document.createElement("div");
        return e.style.width = "100%", t.appendChild(e), ce = t.offsetWidth - e.offsetWidth, t.parentNode.removeChild(t), ce
    }

    const rt = "ontouchend" in document ? "touchstart" : "mousedown";

    function en(t) {
        let e = !1;
        return function (...a) {
            e || (e = !0, requestAnimationFrame(() => {
                e = !1, t.apply(this, a)
            }))
        }
    }

    function W(t, e) {
        return {setup: t, name: t.name, props: e}
    }

    function _(t, e) {
        return new Proxy(t, {
            get(a, o) {
                const l = a[o];
                return l !== void 0 ? l : e[o]
            }
        })
    }

    const z = () => t => t, tn = (t, e) => {
        const n = {};
        for (const a in t) if (Object.prototype.hasOwnProperty.call(t, a)) {
            const o = zt(a);
            let l = t[a];
            e.indexOf(o) !== -1 && l === "" && (l = !0), n[o] = l
        }
        return n
    };

    function nn(t, {slots: e}) {
        const n = _(t, {appendToBody: !0}), a = S(), o = r.ref(null), l = r.ref({left: "", top: ""}), i = () => {
            if (!n.visible || !o.value) return;
            const u = n.getRelativeElement();
            if (!u) return;
            const {width: c, height: f} = qt(o.value);
            l.value = Gt(u, c, f, n.appendToBody)
        };
        r.watchEffect(i, {flush: "post"}), r.watchEffect(u => {
            const c = n.getRelativeElement();
            if (!c) return;
            const f = $e(c) || window, p = en(i);
            f.addEventListener("scroll", p), window.addEventListener("resize", p), u(() => {
                f.removeEventListener("scroll", p), window.removeEventListener("resize", p)
            })
        }, {flush: "post"});
        const s = u => {
            if (!n.visible) return;
            const c = u.target, f = o.value, p = n.getRelativeElement();
            f && !f.contains(c) && p && !p.contains(c) && n.onClickOutside(u)
        };
        return r.watchEffect(u => {
            document.addEventListener(rt, s), u(() => {
                document.removeEventListener(rt, s)
            })
        }), () => r.createVNode(r.Teleport, {
            to: "body",
            disabled: !n.appendToBody
        }, {
            default: () => [r.createVNode(r.Transition, {name: `${a}-zoom-in-down`}, {
                default: () => {
                    var u;
                    return [n.visible && r.createVNode("div", {
                        ref: o,
                        class: `${a}-datepicker-main ${a}-datepicker-popup ${n.className}`,
                        style: [Y({position: "absolute"}, l.value), n.style || {}]
                    }, [(u = e.default) == null ? void 0 : u.call(e)])]
                }
            })]
        })
    }

    const an = z()(["style", "className", "visible", "appendToBody", "onClickOutside", "getRelativeElement"]);
    var rn = W(nn, an);
    const on = {xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 1024 1024", width: "1em", height: "1em"},
        ln = [r.createElementVNode("path", {d: "M940.218 107.055H730.764v-60.51H665.6v60.51H363.055v-60.51H297.89v60.51H83.78c-18.617 0-32.581 13.963-32.581 32.581v805.237c0 18.618 13.964 32.582 32.582 32.582h861.09c18.619 0 32.583-13.964 32.583-32.582V139.636c-4.655-18.618-18.619-32.581-37.237-32.581zm-642.327 65.163v60.51h65.164v-60.51h307.2v60.51h65.163v-60.51h176.873v204.8H116.364v-204.8H297.89zM116.364 912.291V442.18H912.29v470.11H116.364z"}, null, -1)];

    function ot(t, e) {
        return r.openBlock(), r.createElementBlock("svg", on, ln)
    }

    const sn = {xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 1024 1024", width: "1em", height: "1em"},
        cn = [r.createElementVNode("path", {d: "M810.005 274.005 572.011 512l237.994 237.995-60.01 60.01L512 572.011 274.005 810.005l-60.01-60.01L451.989 512 213.995 274.005l60.01-60.01L512 451.989l237.995-237.994z"}, null, -1)];

    function un(t, e) {
        return r.openBlock(), r.createElementBlock("svg", sn, cn)
    }

    const dn = {xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", width: "1em", height: "1em"},
        fn = [r.createElementVNode("path", {
            d: "M0 0h24v24H0z",
            fill: "none"
        }, null, -1), r.createElementVNode("path", {d: "M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"}, null, -1), r.createElementVNode("path", {d: "M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"}, null, -1)];

    function mn(t, e) {
        return r.openBlock(), r.createElementBlock("svg", dn, fn)
    }

    function K(t, e = 0, n = 1, a = 0, o = 0, l = 0, i = 0) {
        const s = new Date(t, e, n, a, o, l, i);
        return t < 100 && t >= 0 && s.setFullYear(t), s
    }

    function I(t) {
        return t instanceof Date && !isNaN(t.getTime())
    }

    function X(t) {
        return Array.isArray(t) && t.length === 2 && t.every(I) && t[0] <= t[1]
    }

    function pn(t) {
        return Array.isArray(t) && t.every(I)
    }

    function ue(...t) {
        if (t[0] !== void 0 && t[0] !== null) {
            const n = new Date(t[0]);
            if (I(n)) return n
        }
        const e = t.slice(1);
        return e.length ? ue(...e) : new Date
    }

    function hn(t) {
        const e = new Date(t);
        return e.setMonth(0, 1), e.setHours(0, 0, 0, 0), e
    }

    function lt(t) {
        const e = new Date(t);
        return e.setDate(1), e.setHours(0, 0, 0, 0), e
    }

    function Z(t) {
        const e = new Date(t);
        return e.setHours(0, 0, 0, 0), e
    }

    function gn({firstDayOfWeek: t, year: e, month: n}) {
        const a = [], o = K(e, n, 0), l = o.getDate(), i = l - (o.getDay() + 7 - t) % 7;
        for (let f = i; f <= l; f++) a.push(K(e, n, f - l));
        o.setMonth(n + 1, 0);
        const s = o.getDate();
        for (let f = 1; f <= s; f++) a.push(K(e, n, f));
        const u = l - i + 1, c = 6 * 7 - u - s;
        for (let f = 1; f <= c; f++) a.push(K(e, n, s + f));
        return a
    }

    function de(t, e) {
        const n = new Date(t), a = typeof e == "function" ? e(n.getMonth()) : Number(e), o = n.getFullYear(),
            l = K(o, a + 1, 0).getDate(), i = n.getDate();
        return n.setMonth(a, Math.min(i, l)), n
    }

    function te(t, e) {
        const n = new Date(t), a = typeof e == "function" ? e(n.getFullYear()) : e;
        return n.setFullYear(a), n
    }

    function yn(t, e) {
        const n = new Date(e), a = new Date(t), o = n.getFullYear() - a.getFullYear(), l = n.getMonth() - a.getMonth();
        return o * 12 + l
    }

    function fe(t, e) {
        const n = new Date(t), a = new Date(e);
        return n.setHours(a.getHours(), a.getMinutes(), a.getSeconds()), n
    }

    function vn(t, {slots: e}) {
        const n = _(t, {editable: !0, disabled: !1, clearable: !0, range: !1, multiple: !1}), a = S(), o = r.ref(null),
            l = r.computed(() => n.separator || (n.range ? " ~ " : ",")),
            i = h => n.range ? X(h) : n.multiple ? pn(h) : I(h),
            s = h => Array.isArray(h) ? h.some(w => n.disabledDate(w)) : n.disabledDate(h),
            u = r.computed(() => o.value !== null ? o.value : typeof n.renderInputText == "function" ? n.renderInputText(n.value) : i(n.value) ? Array.isArray(n.value) ? n.value.map(h => n.formatDate(h)).join(l.value) : n.formatDate(n.value) : ""),
            c = h => {
                var w;
                h && h.stopPropagation(), n.onChange(n.range ? [null, null] : null), (w = n.onClear) == null || w.call(n)
            }, f = () => {
                var v;
                if (!n.editable || o.value === null) return;
                const h = o.value.trim();
                if (o.value = null, h === "") {
                    c();
                    return
                }
                let w;
                if (n.range) {
                    let m = h.split(l.value);
                    m.length !== 2 && (m = h.split(l.value.trim())), w = m.map(d => n.parseDate(d.trim()))
                } else n.multiple ? w = h.split(l.value).map(m => n.parseDate(m.trim())) : w = n.parseDate(h);
                i(w) && !s(w) ? n.onChange(w) : (v = n.onInputError) == null || v.call(n, h)
            }, p = h => {
                o.value = typeof h == "string" ? h : h.target.value
            }, D = h => {
                const {keyCode: w} = h;
                w === 9 ? n.onBlur() : w === 13 && f()
            };
        return () => {
            var v, m, d;
            const h = !n.disabled && n.clearable && u.value, w = A(Y({
                name: "date",
                type: "text",
                autocomplete: "off",
                value: u.value,
                class: n.inputClass || `${a}-input`,
                readonly: !n.editable,
                disabled: n.disabled,
                placeholder: n.placeholder
            }, n.inputAttr), {onFocus: n.onFocus, onKeydown: D, onInput: p, onChange: f});
            return r.createVNode("div", {
                class: `${a}-input-wrapper`,
                onClick: n.onClick
            }, [((v = e.input) == null ? void 0 : v.call(e, w)) || r.createVNode("input", w, null), h ? r.createVNode("i", {
                class: `${a}-icon-clear`,
                onClick: c
            }, [((m = e["icon-clear"]) == null ? void 0 : m.call(e)) || r.createVNode(un, null, null)]) : null, r.createVNode("i", {class: `${a}-icon-calendar`}, [((d = e["icon-calendar"]) == null ? void 0 : d.call(e)) || r.createVNode(ot, null, null)])])
        }
    }

    const Se = z()(["placeholder", "editable", "disabled", "clearable", "inputClass", "inputAttr", "range", "multiple", "separator", "renderInputText", "onInputError", "onClear"]),
        wn = z()(["value", "formatDate", "parseDate", "disabledDate", "onChange", "onFocus", "onBlur", "onClick", ...Se]);
    var Dn = W(vn, wn);

    function bn(t, {slots: e}) {
        var $;
        const n = _(t, {
            prefixClass: "mx",
            valueType: "date",
            format: "YYYY-MM-DD",
            type: "date",
            disabledDate: () => !1,
            disabledTime: () => !1,
            confirmText: "OK"
        });
        Kt(n.prefixClass), Xt((($ = n.formatter) == null ? void 0 : $.getWeek) || De);
        const a = Zt(r.toRef(t, "lang")), o = r.ref(), l = () => o.value, i = r.ref(!1),
            s = r.computed(() => !n.disabled && (typeof n.open == "boolean" ? n.open : i.value)), u = () => {
                var g, b;
                n.disabled || s.value || (i.value = !0, (g = n["onUpdate:open"]) == null || g.call(n, !0), (b = n.onOpen) == null || b.call(n))
            }, c = () => {
                var g, b;
                !s.value || (i.value = !1, (g = n["onUpdate:open"]) == null || g.call(n, !1), (b = n.onClose) == null || b.call(n))
            },
            f = (g, b) => (b = b || n.format, j(n.formatter) && typeof n.formatter.stringify == "function" ? n.formatter.stringify(g, b) : Ce(g, b, {locale: a.value.formatLocale})),
            p = (g, b) => {
                if (b = b || n.format, j(n.formatter) && typeof n.formatter.parse == "function") return n.formatter.parse(g, b);
                const O = new Date;
                return Bt(g, b, {locale: a.value.formatLocale, backupDate: O})
            }, D = g => {
                switch (n.valueType) {
                    case"date":
                        return g instanceof Date ? new Date(g.getTime()) : new Date(NaN);
                    case"timestamp":
                        return typeof g == "number" ? new Date(g) : new Date(NaN);
                    case"format":
                        return typeof g == "string" ? p(g) : new Date(NaN);
                    default:
                        return typeof g == "string" ? p(g, n.valueType) : new Date(NaN)
                }
            }, h = g => {
                if (!I(g)) return null;
                switch (n.valueType) {
                    case"date":
                        return g;
                    case"timestamp":
                        return g.getTime();
                    case"format":
                        return f(g);
                    default:
                        return f(g, n.valueType)
                }
            }, w = r.computed(() => {
                const g = n.value;
                return n.range ? (Array.isArray(g) ? g.slice(0, 2) : [null, null]).map(D) : n.multiple ? (Array.isArray(g) ? g : []).map(D) : D(g)
            }), v = (g, b, O = !0) => {
                var H, L;
                const x = Array.isArray(g) ? g.map(h) : h(g);
                return (H = n["onUpdate:value"]) == null || H.call(n, x), (L = n.onChange) == null || L.call(n, x, b), O && c(), x
            }, m = r.ref(new Date);
        r.watchEffect(() => {
            s.value && (m.value = w.value)
        });
        const d = (g, b) => {
            n.confirm ? m.value = g : v(g, b, !n.multiple && (b === n.type || b === "time"))
        }, y = () => {
            var b;
            const g = v(m.value);
            (b = n.onConfirm) == null || b.call(n, g)
        }, C = g => n.disabledDate(g) || n.disabledTime(g), T = g => {
            var O;
            const {prefixClass: b} = n;
            return r.createVNode("div", {class: `${b}-datepicker-sidebar`}, [(O = e.sidebar) == null ? void 0 : O.call(e, g), (n.shortcuts || []).map((x, H) => r.createVNode("button", {
                key: H,
                "data-index": H,
                type: "button",
                class: `${b}-btn ${b}-btn-text ${b}-btn-shortcut`,
                onClick: () => {
                    var ne;
                    const L = (ne = x.onClick) == null ? void 0 : ne.call(x);
                    L && v(L)
                }
            }, [x.text]))])
        };
        return () => {
            var G, ve;
            const {
                    prefixClass: g,
                    disabled: b,
                    confirm: O,
                    range: x,
                    popupClass: H,
                    popupStyle: L,
                    appendToBody: ne
                } = n, J = {value: m.value, ["onUpdate:value"]: d, emit: v},
                ye = e.header && r.createVNode("div", {class: `${g}-datepicker-header`}, [e.header(J)]),
                k = (e.footer || O) && r.createVNode("div", {class: `${g}-datepicker-footer`}, [(G = e.footer) == null ? void 0 : G.call(e, J), O && r.createVNode("button", {
                    type: "button",
                    class: `${g}-btn ${g}-datepicker-btn-confirm`,
                    onClick: y
                }, [n.confirmText])]), U = (ve = e.content) == null ? void 0 : ve.call(e, J),
                q = (e.sidebar || n.shortcuts) && T(J);
            return r.createVNode("div", {
                ref: o,
                class: {[`${g}-datepicker`]: !0, [`${g}-datepicker-range`]: x, disabled: b}
            }, [r.createVNode(Dn, A(Y({}, F(n, Se)), {
                value: w.value,
                formatDate: f,
                parseDate: p,
                disabledDate: C,
                onChange: v,
                onClick: u,
                onFocus: u,
                onBlur: c
            }), F(e, ["icon-calendar", "icon-clear", "input"])), r.createVNode(rn, {
                className: H,
                style: L,
                visible: s.value,
                appendToBody: ne,
                getRelativeElement: l,
                onClickOutside: c
            }, {default: () => [q, r.createVNode("div", {class: `${g}-datepicker-content`}, [ye, U, k])]})])
        }
    }

    const Cn = [...z()(["value", "valueType", "type", "format", "formatter", "lang", "prefixClass", "appendToBody", "open", "popupClass", "popupStyle", "confirm", "confirmText", "shortcuts", "disabledDate", "disabledTime", "onOpen", "onClose", "onConfirm", "onChange", "onUpdate:open", "onUpdate:value"]), ...Se];
    var it = W(bn, Cn);

    function me(n) {
        var a = n, {value: t} = a, e = Dt(a, ["value"]);
        const o = S();
        return r.createVNode("button", A(Y({}, e), {
            type: "button",
            class: `${o}-btn ${o}-btn-text ${o}-btn-icon-${t}`
        }), [r.createVNode("i", {class: `${o}-icon-${t}`}, null)])
    }

    function xe({type: t, calendar: e, onUpdateCalendar: n}, {slots: a}) {
        var p;
        const o = S(), l = () => {
            n(de(e, D => D - 1))
        }, i = () => {
            n(de(e, D => D + 1))
        }, s = () => {
            n(te(e, D => D - 1))
        }, u = () => {
            n(te(e, D => D + 1))
        }, c = () => {
            n(te(e, D => D - 10))
        }, f = () => {
            n(te(e, D => D + 10))
        };
        return r.createVNode("div", {class: `${o}-calendar-header`}, [r.createVNode(me, {
            value: "double-left",
            onClick: t === "year" ? c : s
        }, null), t === "date" && r.createVNode(me, {
            value: "left",
            onClick: l
        }, null), r.createVNode(me, {
            value: "double-right",
            onClick: t === "year" ? f : u
        }, null), t === "date" && r.createVNode(me, {
            value: "right",
            onClick: i
        }, null), r.createVNode("span", {class: `${o}-calendar-header-label`}, [(p = a.default) == null ? void 0 : p.call(a)])])
    }

    function Mn({
                    calendar: t,
                    isWeekMode: e,
                    showWeekNumber: n,
                    titleFormat: a,
                    getWeekActive: o,
                    getCellClasses: l,
                    onSelect: i,
                    onUpdatePanel: s,
                    onUpdateCalendar: u,
                    onDateMouseEnter: c,
                    onDateMouseLeave: f
                }) {
        const p = S(), D = Jt(), h = Ye().value, {
            yearFormat: w,
            monthBeforeYear: v,
            monthFormat: m = "MMM",
            formatLocale: d
        } = h, y = d.firstDayOfWeek || 0;
        let C = h.days || d.weekdaysMin;
        C = C.concat(C).slice(y, y + 7);
        const T = t.getFullYear(), $ = t.getMonth(), g = ke(gn({firstDayOfWeek: y, year: T, month: $}), 7),
            b = (k, U) => Ce(k, U, {locale: h.formatLocale}), O = k => {
                s(k)
            }, x = k => {
                const U = k.getAttribute("data-index"), [q, G] = U.split(",").map(Un => parseInt(Un, 10)), ve = g[q][G];
                return new Date(ve)
            }, H = k => {
                i(x(k.currentTarget))
            }, L = k => {
                c && c(x(k.currentTarget))
            }, ne = k => {
                f && f(x(k.currentTarget))
            }, J = r.createVNode("button", {
                type: "button",
                class: `${p}-btn ${p}-btn-text ${p}-btn-current-year`,
                onClick: () => O("year")
            }, [b(t, w)]), ye = r.createVNode("button", {
                type: "button",
                class: `${p}-btn ${p}-btn-text ${p}-btn-current-month`,
                onClick: () => O("month")
            }, [b(t, m)]);
        return n = typeof n == "boolean" ? n : e, r.createVNode("div", {class: [`${p}-calendar ${p}-calendar-panel-date`, {[`${p}-calendar-week-mode`]: e}]}, [r.createVNode(xe, {
            type: "date",
            calendar: t,
            onUpdateCalendar: u
        }, {default: () => [v ? [ye, J] : [J, ye]]}), r.createVNode("div", {class: `${p}-calendar-content`}, [r.createVNode("table", {class: `${p}-table ${p}-table-date`}, [r.createVNode("thead", null, [r.createVNode("tr", null, [n && r.createVNode("th", {class: `${p}-week-number-header`}, null), C.map(k => r.createVNode("th", {key: k}, [k]))])]), r.createVNode("tbody", null, [g.map((k, U) => r.createVNode("tr", {
            key: U,
            class: [`${p}-date-row`, {[`${p}-active-week`]: o(k)}]
        }, [n && r.createVNode("td", {
            class: `${p}-week-number`,
            "data-index": `${U},0`,
            onClick: H
        }, [r.createVNode("div", null, [D(k[0])])]), k.map((q, G) => r.createVNode("td", {
            key: G,
            class: ["cell", l(q)],
            title: b(q, a),
            "data-index": `${U},${G}`,
            onClick: H,
            onMouseenter: L,
            onMouseleave: ne
        }, [r.createVNode("div", null, [q.getDate()])]))]))])])])])
    }

    function Tn({calendar: t, getCellClasses: e, onSelect: n, onUpdateCalendar: a, onUpdatePanel: o}) {
        const l = S(), i = Ye().value, s = i.months || i.formatLocale.monthsShort, u = f => K(t.getFullYear(), f),
            c = f => {
                const D = f.currentTarget.getAttribute("data-month");
                n(u(parseInt(D, 10)))
            };
        return r.createVNode("div", {class: `${l}-calendar ${l}-calendar-panel-month`}, [r.createVNode(xe, {
            type: "month",
            calendar: t,
            onUpdateCalendar: a
        }, {
            default: () => [r.createVNode("button", {
                type: "button",
                class: `${l}-btn ${l}-btn-text ${l}-btn-current-year`,
                onClick: () => o("year")
            }, [t.getFullYear()])]
        }), r.createVNode("div", {class: `${l}-calendar-content`}, [r.createVNode("table", {class: `${l}-table ${l}-table-month`}, [ke(s, 3).map((f, p) => r.createVNode("tr", {key: p}, [f.map((D, h) => {
            const w = p * 3 + h;
            return r.createVNode("td", {
                key: h,
                class: ["cell", e(u(w))],
                "data-month": w,
                onClick: c
            }, [r.createVNode("div", null, [D])])
        })]))])])])
    }

    const Vn = t => {
        const e = Math.floor(t.getFullYear() / 10) * 10, n = [];
        for (let a = 0; a < 10; a++) n.push(e + a);
        return ke(n, 2)
    };

    function kn({calendar: t, getCellClasses: e = () => [], getYearPanel: n = Vn, onSelect: a, onUpdateCalendar: o}) {
        const l = S(), i = p => K(p, 0), s = p => {
            const h = p.currentTarget.getAttribute("data-year");
            a(i(parseInt(h, 10)))
        }, u = n(new Date(t)), c = u[0][0], f = Qe(Qe(u));
        return r.createVNode("div", {class: `${l}-calendar ${l}-calendar-panel-year`}, [r.createVNode(xe, {
            type: "year",
            calendar: t,
            onUpdateCalendar: o
        }, {default: () => [r.createVNode("span", null, [c]), r.createVNode("span", {class: `${l}-calendar-decade-separator`}, null), r.createVNode("span", null, [f])]}), r.createVNode("div", {class: `${l}-calendar-content`}, [r.createVNode("table", {class: `${l}-table ${l}-table-year`}, [u.map((p, D) => r.createVNode("tr", {key: D}, [p.map((h, w) => r.createVNode("td", {
            key: w,
            class: ["cell", e(i(h))],
            "data-year": h,
            onClick: s
        }, [r.createVNode("div", null, [h])]))]))])])])
    }

    function Nn(t) {
        const e = _(t, {
                defaultValue: Z(new Date),
                type: "date",
                disabledDate: () => !1,
                getClasses: () => [],
                titleFormat: "YYYY-MM-DD"
            }),
            n = r.computed(() => (Array.isArray(e.value) ? e.value : [e.value]).filter(I).map(y => e.type === "year" ? hn(y) : e.type === "month" ? lt(y) : Z(y))),
            a = r.ref(new Date);
        r.watchEffect(() => {
            let d = e.calendar;
            if (!I(d)) {
                const {length: y} = n.value;
                d = ue(y > 0 ? n.value[y - 1] : e.defaultValue)
            }
            a.value = lt(d)
        });
        const o = d => {
            var y;
            a.value = d, (y = e.onCalendarChange) == null || y.call(e, d)
        }, l = r.ref("date");
        r.watchEffect(() => {
            const d = ["date", "month", "year"], y = Math.max(d.indexOf(e.type), d.indexOf(e.defaultPanel));
            l.value = y !== -1 ? d[y] : "date"
        });
        const i = d => {
                var C;
                const y = l.value;
                l.value = d, (C = e.onPanelChange) == null || C.call(e, d, y)
            }, s = d => e.disabledDate(new Date(d), n.value), u = (d, y) => {
                var C, T, $;
                if (!s(d)) if ((C = e.onPick) == null || C.call(e, d), e.multiple === !0) {
                    const g = n.value.filter(b => b.getTime() !== d.getTime());
                    g.length === n.value.length && g.push(d), (T = e["onUpdate:value"]) == null || T.call(e, g, y)
                } else ($ = e["onUpdate:value"]) == null || $.call(e, d, y)
            }, c = d => {
                u(d, e.type === "week" ? "week" : "date")
            }, f = d => {
                if (e.type === "year") u(d, "year"); else if (o(d), i("month"), e.partialUpdate && n.value.length === 1) {
                    const y = te(n.value[0], d.getFullYear());
                    u(y, "year")
                }
            }, p = d => {
                if (e.type === "month") u(d, "month"); else if (o(d), i("date"), e.partialUpdate && n.value.length === 1) {
                    const y = de(te(n.value[0], d.getFullYear()), d.getMonth());
                    u(y, "month")
                }
            },
            D = (d, y = []) => (s(d) ? y.push("disabled") : n.value.some(C => C.getTime() === d.getTime()) && y.push("active"), y.concat(e.getClasses(d, n.value, y.join(" ")))),
            h = d => {
                const y = d.getMonth() !== a.value.getMonth(), C = [];
                return d.getTime() === new Date().setHours(0, 0, 0, 0) && C.push("today"), y && C.push("not-current-month"), D(d, C)
            }, w = d => e.type !== "month" ? a.value.getMonth() === d.getMonth() ? "active" : "" : D(d),
            v = d => e.type !== "year" ? a.value.getFullYear() === d.getFullYear() ? "active" : "" : D(d), m = d => {
                if (e.type !== "week") return !1;
                const y = d[0].getTime(), C = d[6].getTime();
                return n.value.some(T => {
                    const $ = T.getTime();
                    return $ >= y && $ <= C
                })
            };
        return () => l.value === "year" ? r.createVNode(kn, {
            calendar: a.value,
            getCellClasses: v,
            getYearPanel: e.getYearPanel,
            onSelect: f,
            onUpdateCalendar: o
        }, null) : l.value === "month" ? r.createVNode(Tn, {
            calendar: a.value,
            getCellClasses: w,
            onSelect: p,
            onUpdatePanel: i,
            onUpdateCalendar: o
        }, null) : r.createVNode(Mn, {
            isWeekMode: e.type === "week",
            showWeekNumber: e.showWeekNumber,
            titleFormat: e.titleFormat,
            calendar: a.value,
            getCellClasses: h,
            getWeekActive: m,
            onSelect: c,
            onUpdatePanel: i,
            onUpdateCalendar: o,
            onDateMouseEnter: e.onDateMouseEnter,
            onDateMouseLeave: e.onDateMouseLeave
        }, null)
    }

    const pe = z()(["type", "value", "defaultValue", "defaultPanel", "disabledDate", "getClasses", "calendar", "multiple", "partialUpdate", "showWeekNumber", "titleFormat", "getYearPanel", "onDateMouseEnter", "onDateMouseLeave", "onCalendarChange", "onPanelChange", "onUpdate:value", "onPick"]);
    var he = W(Nn, pe);
    const st = (t, e) => {
        const n = t.getTime();
        let [a, o] = e.map(l => l.getTime());
        return a > o && ([a, o] = [o, a]), n > a && n < o
    };

    function Yn(t) {
        const e = _(t, {defaultValue: new Date, type: "date"}), n = S(), a = r.computed(() => {
            let m = Array.isArray(e.defaultValue) ? e.defaultValue : [e.defaultValue, e.defaultValue];
            return m = m.map(d => Z(d)), X(m) ? m : [new Date, new Date].map(d => Z(d))
        }), o = r.ref([new Date(NaN), new Date(NaN)]);
        r.watchEffect(() => {
            X(e.value) && (o.value = e.value)
        });
        const l = (m, d) => {
                var T;
                const [y, C] = o.value;
                I(y) && !I(C) ? (y.getTime() > m.getTime() ? o.value = [m, y] : o.value = [y, m], (T = e["onUpdate:value"]) == null || T.call(e, o.value, d)) : o.value = [m, new Date(NaN)]
            }, i = r.ref([new Date, new Date]), s = r.computed(() => X(e.calendar) ? e.calendar : i.value),
            u = r.computed(() => e.type === "year" ? 10 * 12 : e.type === "month" ? 1 * 12 : 1), c = (m, d) => {
                var T;
                const y = yn(m[0], m[1]), C = u.value - y;
                if (C > 0) {
                    const $ = d === 1 ? 0 : 1;
                    m[$] = de(m[$], g => g + ($ === 0 ? -C : C))
                }
                i.value = m, (T = e.onCalendarChange) == null || T.call(e, m, d)
            }, f = m => {
                c([m, s.value[1]], 0)
            }, p = m => {
                c([s.value[0], m], 1)
            };
        r.watchEffect(() => {
            const m = X(e.value) ? e.value : a.value;
            c(m.slice(0, 2))
        });
        const D = r.ref(null), h = m => D.value = m, w = () => D.value = null, v = (m, d, y) => {
            const C = e.getClasses ? e.getClasses(m, d, y) : [], T = Array.isArray(C) ? C : [C];
            return /disabled|active/.test(y) ? T : (d.length === 2 && st(m, d) && T.push("in-range"), d.length === 1 && D.value && st(m, [d[0], D.value]) ? T.concat("hover-in-range") : T)
        };
        return () => {
            const m = s.value.map((d, y) => {
                const C = A(Y({}, e), {
                    calendar: d,
                    value: o.value,
                    defaultValue: a.value[y],
                    getClasses: v,
                    partialUpdate: !1,
                    multiple: !1,
                    ["onUpdate:value"]: l,
                    onCalendarChange: y === 0 ? f : p,
                    onDateMouseLeave: w,
                    onDateMouseEnter: h
                });
                return r.createVNode(he, C, null)
            });
            return r.createVNode("div", {class: `${n}-calendar-range`}, [m])
        }
    }

    const Pe = pe;
    var Oe = W(Yn, Pe);
    const ct = r.defineComponent({
        setup(t, {slots: e}) {
            const n = S(), a = r.ref(), o = r.ref(""), l = r.ref(""), i = () => {
                if (!a.value) return;
                const w = a.value, v = w.clientHeight * 100 / w.scrollHeight;
                o.value = v < 100 ? `${v}%` : ""
            };
            r.onMounted(i);
            const s = Qt(), u = w => {
                const v = w.currentTarget, {scrollHeight: m, scrollTop: d} = v;
                l.value = `${d * 100 / m}%`
            };
            let c = !1, f = 0;
            const p = w => {
                w.stopImmediatePropagation();
                const v = w.currentTarget, {offsetTop: m} = v;
                c = !0, f = w.clientY - m
            }, D = w => {
                if (!c || !a.value) return;
                const {clientY: v} = w, {scrollHeight: m, clientHeight: d} = a.value, C = (v - f) * m / d;
                a.value.scrollTop = C
            }, h = () => {
                c = !1
            };
            return r.onMounted(() => {
                document.addEventListener("mousemove", D), document.addEventListener("mouseup", h)
            }), r.onUnmounted(() => {
                document.addEventListener("mousemove", D), document.addEventListener("mouseup", h)
            }), () => {
                var w;
                return r.createVNode("div", {
                    class: `${n}-scrollbar`,
                    style: {position: "relative", overflow: "hidden"}
                }, [r.createVNode("div", {
                    ref: a,
                    class: `${n}-scrollbar-wrap`,
                    style: {marginRight: `-${s}px`},
                    onScroll: u
                }, [(w = e.default) == null ? void 0 : w.call(e)]), r.createVNode("div", {class: `${n}-scrollbar-track`}, [r.createVNode("div", {
                    class: `${n}-scrollbar-thumb`,
                    style: {height: o.value, top: l.value},
                    onMousedown: p
                }, null)])])
            }
        }
    });

    function $n({options: t, getClasses: e, onSelect: n}) {
        const a = S(), o = l => {
            const i = l.target, s = l.currentTarget;
            if (i.tagName.toUpperCase() !== "LI") return;
            const u = s.getAttribute("data-type"), c = parseInt(s.getAttribute("data-index"), 10),
                f = parseInt(i.getAttribute("data-index"), 10), p = t[c].list[f].value;
            n(p, u)
        };
        return r.createVNode("div", {class: `${a}-time-columns`}, [t.map((l, i) => r.createVNode(ct, {
            key: l.type,
            class: `${a}-time-column`
        }, {
            default: () => [r.createVNode("ul", {
                class: `${a}-time-list`,
                "data-index": i,
                "data-type": l.type,
                onClick: o
            }, [l.list.map((s, u) => r.createVNode("li", {
                key: s.text,
                "data-index": u,
                class: [`${a}-time-item`, e(s.value, l.type)]
            }, [s.text]))])]
        }))])
    }

    function Sn(t) {
        return typeof t == "function" || Object.prototype.toString.call(t) === "[object Object]" && !r.isVNode(t)
    }

    function xn(t) {
        let e;
        const n = S();
        return r.createVNode(ct, null, Sn(e = t.options.map(a => r.createVNode("div", {
            key: a.text,
            class: [`${n}-time-option`, t.getClasses(a.value, "time")],
            onClick: () => t.onSelect(a.value, "time")
        }, [a.text]))) ? e : {default: () => [e]})
    }

    function Ae({length: t, step: e = 1, options: n}) {
        if (Array.isArray(n)) return n.filter(o => o >= 0 && o < t);
        e <= 0 && (e = 1);
        const a = [];
        for (let o = 0; o < t; o += e) a.push(o);
        return a
    }

    function Pn(t, e) {
        let {showHour: n, showMinute: a, showSecond: o, use12h: l} = e;
        const i = e.format || "HH:mm:ss";
        n = typeof n == "boolean" ? n : /[HhKk]/.test(i), a = typeof a == "boolean" ? a : /m/.test(i), o = typeof o == "boolean" ? o : /s/.test(i), l = typeof l == "boolean" ? l : /a/i.test(i);
        const s = [], u = l && t.getHours() >= 12;
        return n && s.push({
            type: "hour",
            list: Ae({length: l ? 12 : 24, step: e.hourStep, options: e.hourOptions}).map(c => {
                const f = c === 0 && l ? "12" : Ne(c), p = new Date(t);
                return p.setHours(u ? c + 12 : c), {value: p, text: f}
            })
        }), a && s.push({
            type: "minute", list: Ae({length: 60, step: e.minuteStep, options: e.minuteOptions}).map(c => {
                const f = new Date(t);
                return f.setMinutes(c), {value: f, text: Ne(c)}
            })
        }), o && s.push({
            type: "second", list: Ae({length: 60, step: e.secondStep, options: e.secondOptions}).map(c => {
                const f = new Date(t);
                return f.setSeconds(c), {value: f, text: Ne(c)}
            })
        }), l && s.push({
            type: "ampm", list: ["AM", "PM"].map((c, f) => {
                const p = new Date(t);
                return p.setHours(p.getHours() % 12 + f * 12), {text: c, value: p}
            })
        }), s
    }

    function Ee(t = "") {
        const e = t.split(":");
        if (e.length >= 2) {
            const n = parseInt(e[0], 10), a = parseInt(e[1], 10);
            return {hours: n, minutes: a}
        }
        return null
    }

    function On({date: t, option: e, format: n, formatDate: a}) {
        const o = [];
        if (typeof e == "function") return e() || [];
        const l = Ee(e.start), i = Ee(e.end), s = Ee(e.step), u = e.format || n;
        if (l && i && s) {
            const c = l.minutes + l.hours * 60, f = i.minutes + i.hours * 60, p = s.minutes + s.hours * 60,
                D = Math.floor((f - c) / p);
            for (let h = 0; h <= D; h++) {
                const w = c + h * p, v = Math.floor(w / 60), m = w % 60, d = new Date(t);
                d.setHours(v, m, 0), o.push({value: d, text: a(d, u)})
            }
        }
        return o
    }

    const ut = (t, e, n = 0) => {
        if (n <= 0) {
            requestAnimationFrame(() => {
                t.scrollTop = e
            });
            return
        }
        const o = (e - t.scrollTop) / n * 10;
        requestAnimationFrame(() => {
            const l = t.scrollTop + o;
            if (l >= e) {
                t.scrollTop = e;
                return
            }
            t.scrollTop = l, ut(t, e, n - 10)
        })
    };

    function An(t) {
        const e = _(t, {
            defaultValue: Z(new Date),
            format: "HH:mm:ss",
            timeTitleFormat: "YYYY-MM-DD",
            disabledTime: () => !1,
            scrollDuration: 100
        }), n = S(), a = Ye(), o = (v, m) => Ce(v, m, {locale: a.value.formatLocale}), l = r.ref(new Date);
        r.watchEffect(() => {
            l.value = ue(e.value, e.defaultValue)
        });
        const i = v => Array.isArray(v) ? v.every(m => e.disabledTime(new Date(m))) : e.disabledTime(new Date(v)),
            s = v => {
                const m = new Date(v);
                return i([m.getTime(), m.setMinutes(0, 0, 0), m.setMinutes(59, 59, 999)])
            }, u = v => {
                const m = new Date(v);
                return i([m.getTime(), m.setSeconds(0, 0), m.setSeconds(59, 999)])
            }, c = v => {
                const m = new Date(v), d = m.getHours() < 12 ? 0 : 12, y = d + 11;
                return i([m.getTime(), m.setHours(d, 0, 0, 0), m.setHours(y, 59, 59, 999)])
            }, f = (v, m) => m === "hour" ? s(v) : m === "minute" ? u(v) : m === "ampm" ? c(v) : i(v), p = (v, m) => {
                var d;
                if (!f(v, m)) {
                    const y = new Date(v);
                    l.value = y, i(y) || (d = e["onUpdate:value"]) == null || d.call(e, y, m)
                }
            }, D = (v, m) => f(v, m) ? "disabled" : v.getTime() === l.value.getTime() ? "active" : "", h = r.ref(),
            w = v => {
                if (!h.value) return;
                const m = h.value.querySelectorAll(".active");
                for (let d = 0; d < m.length; d++) {
                    const y = m[d], C = $e(y, h.value);
                    if (C) {
                        const T = y.offsetTop;
                        ut(C, T, v)
                    }
                }
            };
        return r.onMounted(() => w(0)), r.watch(l, () => w(e.scrollDuration), {flush: "post"}), () => {
            let v;
            return e.timePickerOptions ? v = r.createVNode(xn, {
                onSelect: p,
                getClasses: D,
                options: On({date: l.value, format: e.format, option: e.timePickerOptions, formatDate: o})
            }, null) : v = r.createVNode($n, {
                options: Pn(l.value, e),
                onSelect: p,
                getClasses: D
            }, null), r.createVNode("div", {
                class: `${n}-time`,
                ref: h
            }, [e.showTimeHeader && r.createVNode("div", {class: `${n}-time-header`}, [r.createVNode("button", {
                type: "button",
                class: `${n}-btn ${n}-btn-text ${n}-time-header-title`,
                onClick: e.onClickTitle
            }, [o(l.value, e.timeTitleFormat)])]), r.createVNode("div", {class: `${n}-time-content`}, [v])])
        }
    }

    const ge = z()(["value", "defaultValue", "format", "timeTitleFormat", "showTimeHeader", "disabledTime", "timePickerOptions", "hourOptions", "minuteOptions", "secondOptions", "hourStep", "minuteStep", "secondStep", "showHour", "showMinute", "showSecond", "use12h", "scrollDuration", "onClickTitle", "onUpdate:value"]);
    var ie = W(An, ge);

    function En(t) {
        const e = _(t, {defaultValue: Z(new Date), disabledTime: () => !1}), n = S(),
            a = r.ref([new Date(NaN), new Date(NaN)]);
        r.watchEffect(() => {
            X(e.value) ? a.value = e.value : a.value = [new Date(NaN), new Date(NaN)]
        });
        const o = (c, f) => {
            var p;
            (p = e["onUpdate:value"]) == null || p.call(e, a.value, c === "time" ? "time-range" : c, f)
        }, l = (c, f) => {
            a.value[0] = c, a.value[1].getTime() >= c.getTime() || (a.value[1] = c), o(f, 0)
        }, i = (c, f) => {
            a.value[1] = c, a.value[0].getTime() <= c.getTime() || (a.value[0] = c), o(f, 1)
        }, s = c => e.disabledTime(c, 0), u = c => c.getTime() < a.value[0].getTime() || e.disabledTime(c, 1);
        return () => {
            const c = Array.isArray(e.defaultValue) ? e.defaultValue : [e.defaultValue, e.defaultValue];
            return r.createVNode("div", {class: `${n}-time-range`}, [r.createVNode(ie, A(Y({}, e), {
                ["onUpdate:value"]: l,
                value: a.value[0],
                defaultValue: c[0],
                disabledTime: s
            }), null), r.createVNode(ie, A(Y({}, e), {
                ["onUpdate:value"]: i,
                value: a.value[1],
                defaultValue: c[1],
                disabledTime: u
            }), null)])
        }
    }

    const He = ge;
    var Fe = W(En, He);

    function dt(t) {
        const e = r.ref(!1), n = () => {
            var l;
            e.value = !1, (l = t.onShowTimePanelChange) == null || l.call(t, !1)
        }, a = () => {
            var l;
            e.value = !0, (l = t.onShowTimePanelChange) == null || l.call(t, !0)
        };
        return {
            timeVisible: r.computed(() => typeof t.showTimePanel == "boolean" ? t.showTimePanel : e.value),
            openTimePanel: a,
            closeTimePanel: n
        }
    }

    function Hn(t) {
        const e = _(t, {disabledTime: () => !1, defaultValue: Z(new Date)}), n = r.ref(e.value);
        r.watchEffect(() => {
            n.value = e.value
        });
        const {openTimePanel: a, closeTimePanel: o, timeVisible: l} = dt(e), i = (s, u) => {
            var f;
            u === "date" && a();
            let c = fe(s, ue(e.value, e.defaultValue));
            if (e.disabledTime(new Date(c)) && (c = fe(s, e.defaultValue), e.disabledTime(new Date(c)))) {
                n.value = c;
                return
            }
            (f = e["onUpdate:value"]) == null || f.call(e, c, u)
        };
        return () => {
            const s = S(), u = A(Y({}, F(e, pe)), {multiple: !1, type: "date", value: n.value, ["onUpdate:value"]: i}),
                c = A(Y({}, F(e, ge)), {
                    showTimeHeader: !0,
                    value: n.value,
                    ["onUpdate:value"]: e["onUpdate:value"],
                    onClickTitle: o
                });
            return r.createVNode("div", {class: `${s}-date-time`}, [r.createVNode(he, u, null), l.value && r.createVNode(ie, c, null)])
        }
    }

    const ft = z()(["showTimePanel", "onShowTimePanelChange"]), Fn = [...ft, ...pe, ...ge];
    var mt = W(Hn, Fn);

    function Wn(t) {
        const e = _(t, {defaultValue: Z(new Date), disabledTime: () => !1}), n = r.ref(e.value);
        r.watchEffect(() => {
            n.value = e.value
        });
        const {openTimePanel: a, closeTimePanel: o, timeVisible: l} = dt(e), i = (s, u) => {
            var p;
            u === "date" && a();
            const c = Array.isArray(e.defaultValue) ? e.defaultValue : [e.defaultValue, e.defaultValue];
            let f = s.map((D, h) => {
                const w = X(e.value) ? e.value[h] : c[h];
                return fe(D, w)
            });
            if (f[1].getTime() < f[0].getTime() && (f = [f[0], f[0]]), f.some(e.disabledTime) && (f = s.map((D, h) => fe(D, c[h])), f.some(e.disabledTime))) {
                n.value = f;
                return
            }
            (p = e["onUpdate:value"]) == null || p.call(e, f, u)
        };
        return () => {
            const s = S(), u = A(Y({}, F(e, Pe)), {type: "date", value: n.value, ["onUpdate:value"]: i}),
                c = A(Y({}, F(e, He)), {
                    showTimeHeader: !0,
                    value: n.value,
                    ["onUpdate:value"]: e["onUpdate:value"],
                    onClickTitle: o
                });
            return r.createVNode("div", {class: `${s}-date-time-range`}, [r.createVNode(Oe, u, null), l.value && r.createVNode(Fe, c, null)])
        }
    }

    const _n = [...ft, ...He, ...Pe];
    var pt = W(Wn, _n);
    const In = z()(["range", "open", "appendToBody", "clearable", "confirm", "disabled", "editable", "multiple", "partialUpdate", "showHour", "showMinute", "showSecond", "showTimeHeader", "showTimePanel", "showWeekNumber", "use12h"]),
        ht = {
            date: "YYYY-MM-DD",
            datetime: "YYYY-MM-DD HH:mm:ss",
            year: "YYYY",
            month: "YYYY-MM",
            time: "HH:mm:ss",
            week: "w"
        };

    function gt(t, {slots: e}) {
        const n = t.type || "date", a = t.format || ht[n] || ht.date, o = A(Y({}, tn(t, In)), {type: n, format: a});
        return r.createVNode(it, F(o, it.props), Y({
            content: l => {
                if (o.range) {
                    const i = n === "time" ? Fe : n === "datetime" ? pt : Oe;
                    return r.h(i, F(Y(Y({}, o), l), i.props))
                } else {
                    const i = n === "time" ? ie : n === "datetime" ? mt : he;
                    return r.h(i, F(Y(Y({}, o), l), i.props))
                }
            }, ["icon-calendar"]: () => n === "time" ? r.createVNode(mn, null, null) : r.createVNode(ot, null, null)
        }, e))
    }

    var Ln = Object.assign(gt, {
        locale: Ge, install: t => {
            t.component("DatePicker", gt)
        }
    }, {Calendar: he, CalendarRange: Oe, TimePanel: ie, TimeRange: Fe, DateTime: mt, DateTimeRange: pt});
    return Ln
});
