window.formatIndoDate = function (datetime) {
    if (!datetime) return "";

    const date = new Date(datetime);

    const hari = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];

    const bulan = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    const dayName = hari[date.getDay()];
    const day = String(date.getDate()).padStart(2, "0");
    const month = bulan[date.getMonth()];
    const year = date.getFullYear();

    return `${dayName}, ${day} ${month} ${year}`;
};

window.formatIndoTime = function (datetime) {
    if (!datetime) return "";

    const date = new Date(datetime);

    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${hours}:${minutes}`;
};

window.formatIndoDateTime = function (datetime) {
    if (!datetime) return "";

    const date = new Date(datetime);

    const bulan = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    const day = String(date.getDate()).padStart(2, "0");
    const month = bulan[date.getMonth()];
    const year = date.getFullYear();

    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    // offset dalam menit (dibalik tandanya)
    const offset = -date.getTimezoneOffset() / 60;

    let tzLabel = "";

    if (offset === 7) tzLabel = "WIB";
    else if (offset === 8) tzLabel = "WITA";
    else if (offset === 9) tzLabel = "WIT";
    else tzLabel = `UTC${offset >= 0 ? "+" : ""}${offset}`;

    return `${day} ${month} ${year}, ${hours}:${minutes} ${tzLabel}`;
};
