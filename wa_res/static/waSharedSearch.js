class IntuisphereFtSearch{constructor(s){this.m_restriction_keys=[],this.m_current_results=[],this.m_hash_database=s.hash_database,this.m_hash_js=s.hash_js,this.m_json_index=s.json_index,this.m_js_worker=s.js_worker,this.m_worker=null,this.callback=function(){res.success&&console.log("DEFAULT "+res.search_results.join(" ; "))}}static splitQuery(s){for(var e=[],t=(s=(s=(s=s.replace(/[^\x20\x2D0-9A-Z\x5Fa-z\xC0-\xD6\xD8-\xF6\xF8-\xFF]/g,"")).toLowerCase().trim()).normalize("NFD").replace(/[\u0300-\u036f]/g,"")).split(" "),r=0;r<t.length;r++){var i=t[r].trim();0<i.length&&e.push(i)}return e}setRestrictionKeys(s){this.m_restriction_keys=s}results(){return this.m_current_results}search(s){var t=this;null!==this.m_worker&&this.m_worker.terminate(),this.m_worker=new Worker(this.m_js_worker+"?"+this.m_hash_js),this.m_worker.onmessage=function(s){var e=s.data[0];t.m_current_results=e.search_results,t.then(e)},this.m_worker.postMessage([{code:"init",param:{json_index:this.m_json_index+"?"+this.m_hash_database,restriction_keys:this.m_restriction_keys,context_search:s}}])}then(s){this.callback(s)}}IntuisphereFtSearch.OPERATOR_AND=0,IntuisphereFtSearch.OPERATOR_OR=1;