/* Native dynamic block; every visitor-facing label remains editable. */
((wp) => {
  const el = wp.element.createElement;
  const meta = window.bvsDesignMetadata;
  wp.blocks.registerBlockType(meta.name, {...meta,
    edit: ({attributes, setAttributes}) => el(wp.element.Fragment, null,
      el(wp.blockEditor.InspectorControls, null,
        el(wp.components.PanelBody, {title:'Editor labels and messages'}, Object.keys(meta.attributes).map(key => el(wp.components.TextControl, {key, label:key.replace(/([A-Z])/g,' $1'), value:attributes[key], onChange:value=>setAttributes({[key]:value})})))),
      el('div', wp.blockEditor.useBlockProps({className:'bvs-design-admin'}),
        el('h2', null, 'Wrapping design editor'),
        el('p', null, 'Live 3D machine, three editable panels, image and logo uploads, drawing, PNG download and a quote-form attachment. Label translations can be edited in the block settings.'),
        el('p', null, attributes.productionNote))),
    save: () => null
  });
})(window.wp);
