((wp) => {
  const el = wp.element.createElement;
  const {RichText, InspectorControls, useBlockProps} = wp.blockEditor;
  const {TextControl, PanelBody} = wp.components;
  const meta = window.bvsCookieMetadata;
  wp.blocks.registerBlockType(meta.name, {
    ...meta,
    edit: ({attributes, setAttributes}) => {
      const text = (key, tagName, className) => el(RichText, {
        key, identifier:key, tagName, className, value: attributes[key], allowedFormats: [],
        onChange: value => setAttributes({[key]: value}),
      });
      return el(wp.element.Fragment, null,
        el(InspectorControls, null, el(PanelBody, {title:'Privacy link and feedback'},
          ...['privacyUrl','storageError'].map(key => el(TextControl, {
            key, label:key==='privacyUrl'?'Privacy page URL':'Cookie storage error', value:attributes[key],
            onChange:value=>setAttributes({[key]:value}),
          })))),
        el('div', useBlockProps({className:'bvs-cookie-editor'}),
          el('div', {className:'bvs-cookie-inner'},
            el('div', {className:'bvs-cookie-copy'}, text('title','h2'), text('description','p'), text('privacyLabel','p')),
            el('div', {className:'bvs-cookie-actions'}, text('rejectLabel','p','bvs-cookie-editor-button'), text('acceptLabel','p','bvs-cookie-editor-button'), text('closeLabel','p','bvs-cookie-close'))),
          text('settingsLabel','p')));
    },
    save: () => null,
  });
})(window.wp);
