$(()=> {
  "use strict";
  let ucFirst = (text) => {
    return text.charAt(0).toUpperCase() + text.slice(1);
  };

  $('.select2-tags').each((e, item) => {
    // Build associated class.
    new window['VirtualAssemblyField' + ucFirst($(item).attr('data-sf-type'))](item);
  });
});
