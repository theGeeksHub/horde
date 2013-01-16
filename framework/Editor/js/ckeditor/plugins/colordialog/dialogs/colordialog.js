CKEDITOR.dialog.add("colordialog",function(R){var Q=CKEDITOR.dom.element,P=CKEDITOR.document,O=R.lang.colordialog,N,M={type:"html",html:"&nbsp;"},L;function K(){P.getById(x).removeStyle("background-color");N.getContentElement("picker","selectedColor").setValue("");L&&L.removeAttribute("aria-selected");L=null}function J(b){var a=b.data.getTarget(),c;if(a.getName()=="td"&&(c=a.getChild(0).getHtml())){L=a;L.setAttribute("aria-selected",true);N.getContentElement("picker","selectedColor").setValue(c)}}function I(b){b=b.replace(/^#/,"");for(var a=0,d=[];a<=2;a++){d[a]=parseInt(b.substr(a*2,2),16)}var c=0.2126*d[0]+0.7152*d[1]+0.0722*d[2];return"#"+(c>=165?"000":"fff")}var H,G;function F(b){!b.name&&(b=new CKEDITOR.event(b));var a=!/mouse/.test(b.name),d=b.data.getTarget(),c;if(d.getName()=="td"&&(c=d.getChild(0).getHtml())){D(b);a?H=d:G=d;if(a){d.setStyle("border-color",I(c));d.setStyle("border-style","dotted")}P.getById(z).setStyle("background-color",c);P.getById(y).setHtml(c)}}function E(){var a=H.getChild(0).getHtml();H.setStyle("border-color",a);H.setStyle("border-style","solid");P.getById(z).removeStyle("background-color");P.getById(y).setHtml("&nbsp;");H=null}function D(b){var a=!/mouse/.test(b.name),d=a&&H;if(d){var c=d.getChild(0).getHtml();d.setStyle("border-color",c);d.setStyle("border-style","solid")}if(!(H||G)){P.getById(z).removeStyle("background-color");P.getById(y).setHtml("&nbsp;")}}function C(c){var b=c.data,g=b.getTarget(),e,a,f=b.getKeystroke(),d=R.lang.dir=="rtl";switch(f){case 38:if(e=g.getParent().getPrevious()){a=e.getChild([g.getIndex()]);a.focus()}b.preventDefault();break;case 40:if(e=g.getParent().getNext()){a=e.getChild([g.getIndex()]);if(a&&a.type==1){a.focus()}}b.preventDefault();break;case 32:case 13:J(c);b.preventDefault();break;case d?37:39:if(a=g.getNext()){if(a.type==1){a.focus();b.preventDefault(true)}}else{if(e=g.getParent().getNext()){a=e.getChild([0]);if(a&&a.type==1){a.focus();b.preventDefault(true)}}}break;case d?39:37:if(a=g.getPrevious()){a.focus();b.preventDefault(true)}else{if(e=g.getParent().getPrevious()){a=e.getLast();a.focus();b.preventDefault(true)}}break;default:return}}function B(){w=CKEDITOR.dom.element.createFromHtml('<table tabIndex="-1" aria-label="'+O.options+'" role="grid" style="border-collapse:separate;" cellspacing="0"><caption class="cke_voice_label">'+O.options+'</caption><tbody role="presentation"></tbody></table>');w.on("mouseover",F);w.on("mouseout",D);var c=["00","33","66","99","cc","ff"];function b(l,k){for(var j=l;j<l+3;j++){var i=new Q(w.$.insertRow(-1));i.setAttribute("role","row");for(var h=k;h<k+3;h++){for(var g=0;g<6;g++){f(i.$,"#"+c[h]+c[g]+c[j])}}}}function f(j,i){var h=new Q(j.insertCell(-1));h.setAttribute("class","ColorCell");h.setAttribute("tabIndex",-1);h.setAttribute("role","gridcell");h.on("keydown",C);h.on("click",J);h.on("focus",F);h.on("blur",D);h.setStyle("background-color",i);h.setStyle("border","1px solid "+i);h.setStyle("width","14px");h.setStyle("height","14px");var g=A("color_table_cell");h.setAttribute("aria-labelledby",g);h.append(CKEDITOR.dom.element.createFromHtml('<span id="'+g+'" class="cke_voice_label">'+i+"</span>",CKEDITOR.document))}b(0,0);b(3,0);b(0,3);b(3,3);var d=new Q(w.$.insertRow(-1));d.setAttribute("role","row");for(var a=0;a<6;a++){f(d.$,"#"+c[a]+c[a]+c[a])}for(var e=0;e<12;e++){f(d.$,"#000000")}}var A=function(a){return CKEDITOR.tools.getNextId()+"_"+a},z=A("hicolor"),y=A("hicolortext"),x=A("selhicolor"),w;B();return{title:O.title,minWidth:360,minHeight:220,onLoad:function(){N=this},onHide:function(){K();E()},contents:[{id:"picker",label:O.title,accessKey:"I",elements:[{type:"hbox",padding:0,widths:["70%","10%","30%"],children:[{type:"html",html:"<div></div>",onLoad:function(){CKEDITOR.document.getById(this.domId).append(w)},focus:function(){(H||this.getElement().getElementsByTag("td").getItem(0)).focus()}},M,{type:"vbox",padding:0,widths:["70%","5%","25%"],children:[{type:"html",html:"<span>"+O.highlight+'</span>\t\t\t\t\t\t\t\t\t\t\t\t<div id="'+z+'" style="border: 1px solid; height: 74px; width: 74px;"></div>\t\t\t\t\t\t\t\t\t\t\t\t<div id="'+y+'">&nbsp;</div><span>'+O.selected+'</span>\t\t\t\t\t\t\t\t\t\t\t\t<div id="'+x+'" style="border: 1px solid; height: 20px; width: 74px;"></div>'},{type:"text",label:O.selected,labelStyle:"display:none",id:"selectedColor",style:"width: 74px",onChange:function(){try{P.getById(x).setStyle("background-color",this.getValue())}catch(a){K()}}},M,{type:"button",id:"clear",style:"margin-top: 5px",label:O.clear,onClick:K}]}]}]}]}});