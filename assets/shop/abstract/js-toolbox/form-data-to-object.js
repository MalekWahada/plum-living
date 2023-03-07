import deepMerge from './deep-merge.js';

const PATTERNS = {
    validate: /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
    key: /[a-zA-Z0-9_]+|(?=\[\])/g,
    pushed: /^$/,
    indexed: /^\d+$/,
    named: /^[a-zA-Z0-9_]+$/
};

function build (base, key, value) {
    base[key] = value;
    return base;
}

function pushCounter (counters, key) {
    if(counters[key] === undefined){
        counters[key] = 0;
    }
    return counters[key]++;
}

function formDataToObject (formData) {
    const entries = [...formData.entries()];
    const counters = {};
    const output = {};

    entries.forEach(([key, value]) => {
        // Skip invalid keys
        if(!PATTERNS.validate.test(key)){
            return;
        }

        const keys = key.match(PATTERNS.key);
        let k;
        let merge = value;
        let reverseKey = key;

        while((k = keys.pop()) !== undefined) {
            // Adjust reverseKey
            reverseKey = reverseKey.replace(new RegExp("\\[" + k + "\\]$"), '');

            // Pushed
            if(k.match(PATTERNS.pushed)){
                merge = build([], pushCounter(counters, reverseKey), merge);
            }

            // Indexed
            else if(k.match(PATTERNS.indexed)){
                merge = build([], k, merge);
            }

            // Named
            else if(k.match(PATTERNS.named)){
                merge = build({}, k, merge);
            }
        }

        //console.log('output', output, 'merge', merge);

        deepMerge(output, merge, true);
    });

    return output;
}

export default formDataToObject;
