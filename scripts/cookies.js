export var time = {};
time.sec = 100;
time.min = 60 * time.sec;
time.hour = 60 * time.min;
time.day = 24 * time.hour;
time.year = 365 * time.day;
time.yesterday = time.day * (-1);

const feedback_element_time = time.sec * 3;

//Creates a cookie
export function set(name, value, miliseconds = time.min) {
    if (!name || !value) {
        console.log(`Could not create cookie because name or value is empty/null: name=${name}; value=${value}`);
        return false;
    } else {
        let now = new Date();
        let expires = (new Date(now.getTime() + miliseconds)).toUTCString();
        document.cookie = `${name}=${value};expires=${expires};path=/`;
        return true;
        //console.log(document.cookie);
    }
}

//Gets a specific cookie | Success return: object | Fail return: empty object OR false
export function get(cookie_name) {
    let decoded_URI_component = document.cookie ? decodeURIComponent(document.cookie) : "";
    //No cookie is set, so the function returns an empty object/false
    if (!decoded_URI_component) {
        return null;
    }
    let cookie_search = `${cookie_name}=`;
    let cookie = {};
    /*
    console.log(`Nome do cookie procurado: '${cookie_name}'`);
    console.log(`String procurada: '${cookie_search}'`);
    console.log(`Lista de cookies: '${decoded_URI_component}'`);
    */
    if (decoded_URI_component.includes(cookie_search)) {
        let cookie_parts = decoded_URI_component.split(';');
        for (let i = 0; i < cookie_parts.length; i++) {
            if (cookie_parts[i].includes(cookie_name)) {
                let cookie_string = cookie_parts[i];
                let parts = (cookie_string.trim()).split('=');
                if (parts[0] == cookie_name) {
                    let value = parts[1];
                    return value;
                    break;
                }
            }
        }
    } else {
        return false;
    }
}

//Gets all cookies | Success return: object | Fail return: empty object OR false
export function get_all(return_type = 'object') {
    let decoded_URI_component = document.cookie ? decodeURIComponent(document.cookie) : "";
    if (!decoded_URI_component) return return_type == 'object' ? {} : false;
    let cookies_parts = decoded_URI_component.split(';');
    //console.log(cookies_parts);
    let cookies = {};
    cookies_parts.forEach((cookie) => {
        cookie = cookie.trim();
        let parts = cookie.split('=');
        let name = parts[0];
        let value = parts[1];
        cookies[name] = value;
    });
    //console.log(cookies);
    return cookies;
}

//Gets the full cookie line (name=value;...) based on the name
export function get_line(cookie_name) {
    let decoded_URI_component = document.cookie ? decodeURIComponent(document.cookie) : "";
    if (!decoded_URI_component) return null; // No cookies available
    
    let cookie_search = `${cookie_name}=`;
    let cookie_parts = decoded_URI_component.split(';');

    for (let i = 0; i < cookie_parts.length; i++) {
        let cookie_string = cookie_parts[i].trim();
        if (cookie_string.startsWith(cookie_search)) {
            return cookie_string; // Full cookie line
        }
    }

    return null; // Cookie not found
}