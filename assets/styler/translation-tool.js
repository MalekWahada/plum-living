const yaml = require('js-yaml');
const fs = require('fs');
const path = require('path');
const isCLI = require.main === module; // Called directly from CLI

const outputJSON = path.join(__dirname, '/src/assets/locale/fr/translation.json');

function process(rootPath) {
  const inputYML = path.join(rootPath, '/translations/react.fr.yaml');

  const obj = yaml.load(fs.readFileSync(inputYML, {encoding: 'utf-8'}));
  fs.writeFileSync(outputJSON, JSON.stringify(obj, null, 2));
  console.log('Plum Styler: Translation files have been compiled...');
}

module.exports = { process };

if (isCLI) {
  process(path.resolve(__dirname, '..', '..')); // Called from package directory
}
