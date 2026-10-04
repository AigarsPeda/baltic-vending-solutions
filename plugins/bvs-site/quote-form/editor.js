/* Uses WordPress-provided packages, with no compilation step. */
((wp) => {
  const el = wp.element.createElement;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const { TextControl, TextareaControl, PanelBody, Notice } = wp.components;
  const meta = window.bvsQuoteMetadata;
  wp.blocks.registerBlockType(meta.name, {
    ...meta,
    edit: ({ attributes, setAttributes }) => {
      const control = (key) => el(key.endsWith('Text') || key.endsWith('Message') ? TextareaControl : TextControl, {
        key, label: key.replace(/([A-Z])/g, ' $1').replace(/^./, c => c.toUpperCase()), value: attributes[key] || '',
        onChange: value => setAttributes({[key]: value})
      });
      const groups = {
        'Form and feedback': ['intro','submitLabel','sendingLabel','successMessage','errorMessage','unavailableMessage','formId','language'],
        'Privacy': ['privacyText','privacyLabel','privacyUrl'],
        'Design attachment': ['designLabel','designHint','createDesignLabel','createDesignUrl','designError','designRemove','designEditorLabel','designEditorHint'],
        'Equipment and purchase options': ['modelLabel','modelAny','compactLabel','fridgeLabel','modeLabel','modeAdvice','modeBuy','modeRent'],
        'Field labels and examples': ['nameLabel','namePlaceholder','companyLabel','companyPlaceholder','emailLabel','emailPlaceholder','phoneLabel','phonePlaceholder','productsLabel','productsPlaceholder','locationLabel','locationPlaceholder','messageLabel','messagePlaceholder']
      };
      return el(wp.element.Fragment, null,
        el(InspectorControls, null, Object.entries(groups).map(([title, keys], i) => el(PanelBody, {title, initialOpen: i === 0, key:title}, keys.map(control)))),
        el('div', useBlockProps({className:'bvs-quote-editor'}),
          el(Notice, {status:'info', isDismissible:false}, 'Quote form. Edit all labels, examples and feedback in the block settings. The public form saves private enquiries.'),
          control('intro'),
          el('div', {className:'bvs-form-grid'}, ['name','company','email','phone','products','location','message'].map(key => el('div', {key}, control(key+'Label'), el('div', {className:'bvs-editor-field'}, attributes[key+'Placeholder'])))),
          control('submitLabel'), control('privacyText')
        )
      );
    },
    save: () => null
  });
})(window.wp);
