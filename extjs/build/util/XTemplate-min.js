/*
 * Ext JS Library 2.0 Beta 1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

Ext.XTemplate=function(){Ext.XTemplate.superclass.constructor.apply(this,arguments);var P=this.html;P=["<tpl>",P,"</tpl>"].join("");var O=/<tpl\b[^>]*>((?:(?=([^<]+))\2|<(?!tpl\b[^>]*>))*?)<\/tpl>/;var N=/^<tpl\b[^>]*?for="(.*?)"/;var L=/^<tpl\b[^>]*?if="(.*?)"/;var J=/^<tpl\b[^>]*?exec="(.*?)"/;var C,B=0;var G=[];while(C=P.match(O)){var M=C[0].match(N);var K=C[0].match(L);var I=C[0].match(J);var E=null,H=null,D=null;var A=M&&M[1]?M[1]:"";if(K){E=K&&K[1]?K[1]:null;if(E){H=new Function("values","parent","xindex","xcount","with(values){ return "+(Ext.util.Format.htmlDecode(E))+"; }")}}if(I){E=I&&I[1]?I[1]:null;if(E){D=new Function("values","parent","xindex","xcount","with(values){ "+(Ext.util.Format.htmlDecode(E))+"; }")}}if(A){switch(A){case".":A=new Function("values","parent","with(values){ return values; }");break;case"..":A=new Function("values","parent","with(values){ return parent; }");break;default:A=new Function("values","parent","with(values){ return "+A+"; }")}}G.push({id:B,target:A,exec:D,test:H,body:C[1]||""});P=P.replace(C[0],"{xtpl"+B+"}");++B}for(var F=G.length-1;F>=0;--F){this.compileTpl(G[F])}this.master=G[G.length-1];this.tpls=G};Ext.extend(Ext.XTemplate,Ext.Template,{re:/\{([\w-\.\#]+)(?:\:([\w\.]*)(?:\((.*?)?\))?)?(\s?[\+\-\*\\]\s?[\d\.\+\-\*\\\(\)]+)?\}/g,codeRe:/\{\[((?:\\\]|.|\n)*?)\]\}/g,applySubTemplate:function(A,H,G,D,C){var J=this.tpls[A];if(J.test&&!J.test.call(this,H,G,D,C)){return""}if(J.exec&&J.exec.call(this,H,G,D,C)){return""}var I=J.target?J.target.call(this,H,G):H;G=J.target?H:G;if(J.target&&I instanceof Array){var B=[];for(var E=0,F=I.length;E<F;E++){B[B.length]=J.compiled.call(this,I[E],G,E+1,F)}return B.join("")}return J.compiled.call(this,I,G,D,C)},compileTpl:function(tpl){var fm=Ext.util.Format;var useF=this.disableFormats!==true;var sep=Ext.isGecko?"+":",";var fn=function(m,name,format,args,math){if(name.substr(0,4)=="xtpl"){return"'"+sep+"this.applySubTemplate("+name.substr(4)+", values, parent, xindex, xcount)"+sep+"'"}var v;if(name==="."){v="values"}else{if(name==="#"){v="xindex"}else{if(name.indexOf(".")!=-1){v=name}else{v="values['"+name+"']"}}}if(math){v="("+v+math+")"}if(format&&useF){args=args?","+args:"";if(format.substr(0,5)!="this."){format="fm."+format+"("}else{format="this.call(\""+format.substr(5)+"\", ";args=", values"}}else{args="";format="("+v+" === undefined ? '' : "}return"'"+sep+format+v+args+")"+sep+"'"};var codeFn=function(m,code){return"'"+sep+"("+code+")"+sep+"'"};var body;if(Ext.isGecko){body="tpl.compiled = function(values, parent, xindex, xcount){ return '"+tpl.body.replace(/(\r\n|\n)/g,"\\n").replace(/'/g,"\\'").replace(this.re,fn).replace(this.codeRe,codeFn)+"';};"}else{body=["tpl.compiled = function(values, parent, xindex, xcount){ return ['"];body.push(tpl.body.replace(/(\r\n|\n)/g,"\\n").replace(/'/g,"\\'").replace(this.re,fn).replace(this.codeRe,codeFn));body.push("'].join('');};");body=body.join("")}eval(body);return this},apply:function(A){return this.master.compiled.call(this,A,{},1,1)},applyTemplate:function(A){return this.master.compiled.call(this,A,{},1,1)},compile:function(){return this}});Ext.XTemplate.from=function(A){A=Ext.getDom(A);return new Ext.XTemplate(A.value||A.innerHTML)};