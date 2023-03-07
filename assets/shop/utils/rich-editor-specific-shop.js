const richEditorTexts = document.getElementsByClassName('rich-editor__text');
const iFramePopups = document.getElementsByClassName('cms__i-frame-popup');

function addTargets(texts) {
  const linkTags = texts.getElementsByTagName('a');
  if (linkTags.length > 0) {
    for (let i = 0; i < linkTags.length; i++) {
      linkTags[i].setAttribute('target', '_blank');
    }
  }
}

if (richEditorTexts.length > 0) {
  for (let i = 0; i < richEditorTexts.length; i++) {
    addTargets(richEditorTexts[i]);
  }
}

if (iFramePopups.length > 0) {
  for (let i = 0; i < iFramePopups.length; i++) {
    addTargets(iFramePopups[i]);
  }
}
