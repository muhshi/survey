const fs = require('fs');
const path = require('path');

const pendalamanPath = path.join(__dirname, 'database/surveys/pendalaman.json');
const pendalaman = fs.existsSync(pendalamanPath) ? require(pendalamanPath) : {};

module.exports = {
    pendalaman,
};

