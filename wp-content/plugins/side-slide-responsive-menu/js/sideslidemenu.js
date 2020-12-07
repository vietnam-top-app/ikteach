function sideSlideMenuJS() {

	var self = this;
	this.menuWrap = document.getElementById("sideSlideMenu");
	this.menuToggle = document.getElementById("sideSlideToggle");
	this.ssmContentWrap;
	this.bodyClass = document.body.className;
	this.showSSMsubmenuItems =  document.querySelectorAll('div.ssmenuSubmenuToggle');
	this.SSMdisplayed = false;

	//initialize the plugin
	this.init = function() {

		//wrap whole content in a div
		this.ssmContentWrap = document.createElement("div");
		this.ssmContentWrap.id = "ssmContentWrap";

		// Move the body's children into this wrapper
		while (document.body.firstChild)
		{
		    this.ssmContentWrap.appendChild(document.body.firstChild);
		}

		// Append the wrapper to the body
		document.body.appendChild(this.ssmContentWrap);

		document.body.appendChild(this.menuWrap);
		document.body.appendChild(this.menuToggle);

		this.menuToggle.onclick = function() {
			if (self.SSMdisplayed) {
				//hide menu
				self.hideSSMenu();
			} else {
				self.showSSMenu();
			}
		};

		window.onresize = function() {
			if (self.SSMdisplayed)
				self.hideSSMenu();
		};

	    for(var i=0; i < this.showSSMsubmenuItems.length; i++){
	    	this.showSSMsubmenuItems[i].submenushown = false;
	     	this.showSSMsubmenuItems[i].addEventListener('click', this.showSubmenuClick, false);
	    }

	    //add touch gestures
		/*Hammer(document.body).on("swiperight", function(event) {
			if (!self.SSMdisplayed) 
		        self.showSSMenu();
		});*/

		Hammer(self.menuWrap).on("swipeleft", function(event) {
			if (self.SSMdisplayed) 
		        self.hideSSMenu();
		})
		
	}

	//shows or hides submenus
	this.showSubmenuClick = function() {
		if (this.submenushown) {
			//hide submenu
			this.submenushown = false;
			this.children[0].className = 'fa fa-plus-square-o';
			this.nextSibling.className='ssmenu-submenu';
		} else {
			//show submenu
			this.submenushown = true;
			this.children[0].className = 'fa fa-minus-square-o';
			this.nextSibling.className='ssmenu-submenu ssmenu-submenu-displayed';
		}
	};

	//slide hide menu panel
	this.hideSSMenu = function () {
		self.menuWrap.className = 'sideSlideSlideNegative';
		self.menuToggle.className = 'sideSlideSlideToggleNegative';
		self.ssmContentWrap.className = '';
		self.SSMdisplayed = false;		
	}

	//slide show menu panel
	this.showSSMenu = function () {
		//show menu 
		self.menuWrap.className = 'sideSlideSlidePositive';
		self.menuToggle.className = 'sideSlideSlideTogglePositive';
		self.ssmContentWrap.className = 'sideSlideSlidePositivePadding';
		self.SSMdisplayed = true;		
	}

	//toggle
	this.toggleSSMenu = function() {
		if (self.SSMdisplayed) {
			//hide menu
			self.hideSSMenu();
		} else {
			self.showSSMenu();
		}
	}

}