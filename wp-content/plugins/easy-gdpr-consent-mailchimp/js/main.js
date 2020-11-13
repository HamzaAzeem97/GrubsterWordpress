/**
 * EGCF Form class - API likely to change
 */
var EGCF_Form = function($) {
	"use strict";

	var formEle,
		form,
		modal,
		origSubmit,
		template,
		configs;

	var self = {

		hasConsent: false,

		init: function(theForm, conf) 
		{
			formEle = $(theForm);
			form = formEle.get(0);
			template = conf.tpl,
			configs  = conf;

			// Clone old handler - if any - into origSubmit
			var origSubmit = function() {};
			if (typeof form.onsubmit == 'function') {
				origSubmit = form.onsubmit.bind(form);
			}
			
			var submit = function(e) {

				if (this.hasConsent) {
					origSubmit.call(form);
				}
				else {
					e.preventDefault();
					e.stopImmediatePropagation();

					this.openModal();

					// Also used in jQuery events
					return false;
				}
			};

			form.target   = '';
			form.onsubmit = submit.bind(this);

			// Some devs bind to click handlers for submit instead of submit event 
			formEle.find('[type=submit], [name=submit]').on('click', submit.bind(this));
		},

		/**
		 * Simulate a click for a submit button first for buggy/wrong implementations
		 */
		submitForm: function() 
		{
			var submitBtn = formEle.find('[type=submit], [name=submit]').first();
			
			// Doesn't trigger sometimes for AJAX forms - give it a little delay
			setTimeout(function() {
				if (submitBtn.length) {
					submitBtn.click();
				}
				else {
					formEle.submit();
				}

			}, 1);	
		},

		/**
		 * Setup, process and display modal
		 */
		openModal: function() 
		{
			this.setupModal();

			// Test via AJAX if form should be shown
			if (configs.showTest) {
				
				var postData = {
					action: 'egcf_should_show_form', 
					egcf_form_id: configs.id
				};

				this.toggleSubmitLabel();

				$.post(EGCF_Plugin.ajax_url, postData, function(resp) {
					if (resp.data.show) {
						modal.open();
					}
					else {
						self.hasConsent = true;
						self.submitForm();
					}

					self.toggleSubmitLabel();
				});
			}
			else {
				modal.open();
			}
		},

		/**
		 * Swap label for button and enable/disable it 
		 */
		toggleSubmitLabel: function(theForm) {
			
			theForm = theForm || formEle;
			var submitBtn = theForm.find('[type=submit], [name=submit]').first();
			
			if (submitBtn.length) {

				var method = submitBtn.is('button') ? submitBtn.text : submitBtn.val;

				if (submitBtn.hasClass('egcf-working')) {
					submitBtn.prop('disabled', 0).removeClass('egcf-working');
					method.call(submitBtn, submitBtn.data('origLabel'));
				}
				else {
					submitBtn.prop('disabled', 1)
						.addClass('egcf-working')
						.data('origLabel', method.call(submitBtn));

					method.call(submitBtn, configs.processLabel);
				}
			}
		},

		/**
		 * Initialize modal
		 */
		setupModal: function() 
		{
			modal = new tingle.modal({
				footer: false,
				cssClass: ['egcf-modal'],
				closeMethods: ['escape'],
			});

			// Make it available in modal
			formEle.data('modal', modal);

			modal.setContent(template.html());

			var inner = $(modal.getContent()),
				modalForm = inner.find('form');

			$(modal.modalBox)
				.removeClass('.tingle-modal-box')
				.addClass('egcf-modal-box');

			$(modal.modalBoxContent).addClass('egcf-modal-box-content');

			inner.find('.egcf-modal-close, .egcf-modal-submit .cancel')
				.on('click', function() {
					modal.close();
					return false;
				});
				
			modalForm.on('submit', function(e) {
				return self.submitModal.call(this, e, modalForm);
			});

			return modal;
		},

		/**
		 * Handle form submitted via Modal
		 */
		submitModal: function(e, modalForm) 
		{

			var postData = modalForm.serializeArray();
			postData.push({name: 'action', value: 'egcf_submit_consent'});

			// Email field
			var emailField = modalForm.data('email'),
				email = formEle.find('[name="' + emailField + '"]');
			
			if (email.length) {
				email = email.val();
			}
			else {
				email = '';
			}

			postData.push({name: 'email', value: email});

			self.toggleSubmitLabel(modalForm);

			// Submit the modal form
			$.post(EGCF_Plugin.ajax_url, postData, function(resp) {

				if (resp.success) {
					self.hasConsent = true;
					formEle.data('has-consent', true);

					// Add hidden fields
					var fields = resp.data.fields;
					Object.keys(fields).forEach(function(key) {

						var input = formEle.find('[name="' + key + '"]');
						if (input.length) {
							input.val(fields[key]);
						}
						else {
							formEle.append($("<input type='hidden' name='" + key + "' value='" + fields[key] + "'>"));
						}
					});

					modal.close();
					self.submitForm();

					self.toggleSubmitLabel(modalForm);
					//self.toggleSubmitLabel();
				}
			});

			e.preventDefault();
		}
	};

	return self;
};

jQuery(function($) {

	// renable forms
	$('#egcf-disable-submit').remove();

	$('.egcf-consent-tpl').each(function() {

		var tpl      = $(this),
			selector = tpl.data('selector'),
			config   = $.extend(tpl.data(), {'tpl': tpl});

		$(selector).each(function() {

			var form = $(this);

			if (!form.is('form')) {
				form = form.find('form');
			}

			if (!form.length) {
				return;
			}

			var obj = new EGCF_Form($);
			obj.init(form, config);
		});
	});
});

// Tingle Modal
!function(t,o){"function"==typeof define&&define.amd?define(o):"object"==typeof exports?module.exports=o():t.tingle=o()}(this,function(){function t(t){var o={onClose:null,onOpen:null,beforeOpen:null,beforeClose:null,stickyFooter:!1,footer:!1,cssClass:[],closeLabel:"Close",closeMethods:["overlay","button","escape"]};this.opts=r({},o,t),this.init()}function o(){this.modalBoxFooter&&(this.modalBoxFooter.style.width=this.modalBox.clientWidth+"px",this.modalBoxFooter.style.left=this.modalBox.offsetLeft+"px")}function e(){this.modal=document.createElement("div"),this.modal.classList.add("tingle-modal"),0!==this.opts.closeMethods.length&&-1!==this.opts.closeMethods.indexOf("overlay")||this.modal.classList.add("tingle-modal--noOverlayClose"),this.modal.style.display="none",this.opts.cssClass.forEach(function(t){"string"==typeof t&&this.modal.classList.add(t)},this),-1!==this.opts.closeMethods.indexOf("button")&&(this.modalCloseBtn=document.createElement("button"),this.modalCloseBtn.classList.add("tingle-modal__close"),this.modalCloseBtnIcon=document.createElement("span"),this.modalCloseBtnIcon.classList.add("tingle-modal__closeIcon"),this.modalCloseBtnIcon.innerHTML="×",this.modalCloseBtnLabel=document.createElement("span"),this.modalCloseBtnLabel.classList.add("tingle-modal__closeLabel"),this.modalCloseBtnLabel.innerHTML=this.opts.closeLabel,this.modalCloseBtn.appendChild(this.modalCloseBtnIcon),this.modalCloseBtn.appendChild(this.modalCloseBtnLabel)),this.modalBox=document.createElement("div"),this.modalBox.classList.add("tingle-modal-box"),this.modalBoxContent=document.createElement("div"),this.modalBoxContent.classList.add("tingle-modal-box__content"),this.modalBox.appendChild(this.modalBoxContent),-1!==this.opts.closeMethods.indexOf("button")&&this.modal.appendChild(this.modalCloseBtn),this.modal.appendChild(this.modalBox)}function s(){this.modalBoxFooter=document.createElement("div"),this.modalBoxFooter.classList.add("tingle-modal-box__footer"),this.modalBox.appendChild(this.modalBoxFooter)}function i(){this._events={clickCloseBtn:this.close.bind(this),clickOverlay:l.bind(this),resize:this.checkOverflow.bind(this),keyboardNav:n.bind(this)},-1!==this.opts.closeMethods.indexOf("button")&&this.modalCloseBtn.addEventListener("click",this._events.clickCloseBtn),this.modal.addEventListener("mousedown",this._events.clickOverlay),window.addEventListener("resize",this._events.resize),document.addEventListener("keydown",this._events.keyboardNav)}function n(t){-1!==this.opts.closeMethods.indexOf("escape")&&27===t.which&&this.isOpen()&&this.close()}function l(t){-1!==this.opts.closeMethods.indexOf("overlay")&&!d(t.target,"tingle-modal")&&t.clientX<this.modal.clientWidth&&this.close()}function d(t,o){for(;(t=t.parentElement)&&!t.classList.contains(o););return t}function a(){-1!==this.opts.closeMethods.indexOf("button")&&this.modalCloseBtn.removeEventListener("click",this._events.clickCloseBtn),this.modal.removeEventListener("mousedown",this._events.clickOverlay),window.removeEventListener("resize",this._events.resize),document.removeEventListener("keydown",this._events.keyboardNav)}function r(){for(var t=1;t<arguments.length;t++)for(var o in arguments[t])arguments[t].hasOwnProperty(o)&&(arguments[0][o]=arguments[t][o]);return arguments[0]}var h=function(){var t,o=document.createElement("tingle-test-transition"),e={transition:"transitionend",OTransition:"oTransitionEnd",MozTransition:"transitionend",WebkitTransition:"webkitTransitionEnd"};for(t in e)if(void 0!==o.style[t])return e[t]}();return t.prototype.init=function(){this.modal||(e.call(this),i.call(this),document.body.insertBefore(this.modal,document.body.firstChild),this.opts.footer&&this.addFooter())},t.prototype.destroy=function(){null!==this.modal&&(a.call(this),this.modal.parentNode.removeChild(this.modal),this.modal=null)},t.prototype.open=function(){var t=this;"function"==typeof t.opts.beforeOpen&&t.opts.beforeOpen(),this.modal.style.removeProperty?this.modal.style.removeProperty("display"):this.modal.style.removeAttribute("display"),this._scrollPosition=window.pageYOffset,document.body.classList.add("tingle-enabled"),document.body.style.top=-this._scrollPosition+"px",this.setStickyFooter(this.opts.stickyFooter),this.modal.classList.add("tingle-modal--visible"),h?this.modal.addEventListener(h,function o(){"function"==typeof t.opts.onOpen&&t.opts.onOpen.call(t),t.modal.removeEventListener(h,o,!1)},!1):"function"==typeof t.opts.onOpen&&t.opts.onOpen.call(t),this.checkOverflow()},t.prototype.isOpen=function(){return!!this.modal.classList.contains("tingle-modal--visible")},t.prototype.close=function(){if("function"==typeof this.opts.beforeClose){if(!this.opts.beforeClose.call(this))return}document.body.classList.remove("tingle-enabled"),window.scrollTo(0,this._scrollPosition),document.body.style.top=null,this.modal.classList.remove("tingle-modal--visible");var t=this;h?this.modal.addEventListener(h,function o(){t.modal.removeEventListener(h,o,!1),t.modal.style.display="none","function"==typeof t.opts.onClose&&t.opts.onClose.call(this)},!1):(t.modal.style.display="none","function"==typeof t.opts.onClose&&t.opts.onClose.call(this))},t.prototype.setContent=function(t){"string"==typeof t?this.modalBoxContent.innerHTML=t:(this.modalBoxContent.innerHTML="",this.modalBoxContent.appendChild(t)),this.isOpen()&&this.checkOverflow()},t.prototype.getContent=function(){return this.modalBoxContent},t.prototype.addFooter=function(){s.call(this)},t.prototype.setFooterContent=function(t){this.modalBoxFooter.innerHTML=t},t.prototype.getFooterContent=function(){return this.modalBoxFooter},t.prototype.setStickyFooter=function(t){this.isOverflow()||(t=!1),t?this.modalBox.contains(this.modalBoxFooter)&&(this.modalBox.removeChild(this.modalBoxFooter),this.modal.appendChild(this.modalBoxFooter),this.modalBoxFooter.classList.add("tingle-modal-box__footer--sticky"),o.call(this),this.modalBoxContent.style["padding-bottom"]=this.modalBoxFooter.clientHeight+20+"px"):this.modalBoxFooter&&(this.modalBox.contains(this.modalBoxFooter)||(this.modal.removeChild(this.modalBoxFooter),this.modalBox.appendChild(this.modalBoxFooter),this.modalBoxFooter.style.width="auto",this.modalBoxFooter.style.left="",this.modalBoxContent.style["padding-bottom"]="",this.modalBoxFooter.classList.remove("tingle-modal-box__footer--sticky")))},t.prototype.addFooterBtn=function(t,o,e){var s=document.createElement("button");return s.innerHTML=t,s.addEventListener("click",e),"string"==typeof o&&o.length&&o.split(" ").forEach(function(t){s.classList.add(t)}),this.modalBoxFooter.appendChild(s),s},t.prototype.resize=function(){console.warn("Resize is deprecated and will be removed in version 1.0")},t.prototype.isOverflow=function(){var t=window.innerHeight;return this.modalBox.clientHeight>=t},t.prototype.checkOverflow=function(){this.modal.classList.contains("tingle-modal--visible")&&(this.isOverflow()?this.modal.classList.add("tingle-modal--overflow"):this.modal.classList.remove("tingle-modal--overflow"),!this.isOverflow()&&this.opts.stickyFooter?this.setStickyFooter(!1):this.isOverflow()&&this.opts.stickyFooter&&(o.call(this),this.setStickyFooter(!0)))},{modal:t}});
