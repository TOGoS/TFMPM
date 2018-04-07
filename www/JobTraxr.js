window.JobTraxr = (function() {
	var JobTraxr = function(params) {
		this.ul = params.ul;
		this.nextJobId = 0;
		this.jobs = {};
		this.jobElements = {};
	}
	JobTraxr.prototype.updateCursor = function() {
		let isGlassy = false;
		for( let j in this.jobs ) {
			if( this.jobs[j].isGlassy ) isGlassy = true;
		}
		document.body.style.cursor = isGlassy ? 'wait' : '';
	}
	JobTraxr.prototype.addJob = function(attrs) {
		let jobId = attrs.id || this.nextJobId++;
		this.jobs[jobId] = attrs;
		let jobLi = document.createElement('li');
		jobLi.appendChild(document.createTextNode(attrs.description));
		this.ul.appendChild(jobLi);
		this.jobElements[jobId] = jobLi;
		this.updateCursor();
		return jobId;
	}
	JobTraxr.prototype.removeJob = function(jobId) {
		delete this.jobs[jobId];
		let elem = this.jobElements[jobId];
		if( elem ) elem.parentNode.removeChild(elem);
		delete this.jobElements[jobId];
		this.updateCursor();
	}
	return JobTraxr;
})();
