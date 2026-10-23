// Reads and writes the `Version:` line in a plugin or theme header.
// The leading `*` is optional, so theme `style.css` headers also match.
// Group 1 keeps the key and its padding, so the header stays aligned.
const versionRegex = /^([ \t]*\*?[ \t]*Version:[ \t]*)(\d+\.\d+\.\d+)/m;

module.exports.readVersion = function ( contents ) {
	const found = contents.match( versionRegex );
	if ( ! found ) {
		throw new Error( 'No "Version:" line found in the file header.' );
	}
	return found[ 2 ];
};

module.exports.writeVersion = function ( contents, version ) {
	return contents.replace(
		versionRegex,
		( _match, prefix ) => prefix + version
	);
};
