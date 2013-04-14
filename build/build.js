var fs = require('fs'),
	uglifyjs = require('uglify-js'),
	deps = require('./deps.js').deps;

exports.getFiles = function (compsBase32) {
	var memo = {},
		comps,
		i,
		files = [],
		src;

	if (compsBase32) {
		comps = parseInt(compsBase32, 32).toString(2).split('');
		console.log('Managing dependencies...');
	}

	function addFiles(srcs) {
		var j,
			len;
		for (j = 0, len = srcs.length; j < len; j += 1) {
			memo[srcs[j]] = true;
		}
	}

	for (i in deps) {
		if (deps.hasOwnProperty(i)) {
			if (comps) {
				if (parseInt(comps.pop(), 2) === 1) {
					console.log('\t* ' + i);
					addFiles(deps[i].src);
				} else {
					console.log('\t  ' + i);
				}
			} else {
				addFiles(deps[i].src);
			}
		}
	}

	for (src in memo) {
		if (memo.hasOwnProperty(src)) {
			files.push('src/' + src);
		}
	}

	return files;
};

exports.uglify = function (code) {
	var ast,
		compressor;
	ast = uglifyjs.parse(code);

	// compressor needs figure_out_scope too
	ast.figure_out_scope();
	compressor = uglifyjs.Compressor();
	ast = ast.transform(compressor);

	// need to figure out scope again so mangler works optimally
	ast.figure_out_scope();
	ast.compute_char_frequency();
	ast.mangle_names();

	// get Ugly code back :)
	return ast.print_to_string();
};

exports.combineFiles = function (files) {
	var content = '(function () {\n\n',
		i,
		len;
	for (i = 0, len = files.length; i < len; i += 1) {
		content += fs.readFileSync(files[i], 'utf8') + '\n\n';
	}
	return content + '\n\n}(this));';
};

exports.save = function (savePath, compressed) {
	return fs.writeFileSync(savePath, compressed, 'utf8');
};

exports.load = function (loadPath) {
	try {
		return fs.readFileSync(loadPath, 'utf8');
	} catch (e) {
		return null;
	}
};

exports.getSizeDelta = function (newContent, oldContent) {
	if (!oldContent) {
		return 'new';
	}
	var delta = newContent.length - oldContent.length;
	return (delta >= 0 ? '+' : '') + delta;
};
