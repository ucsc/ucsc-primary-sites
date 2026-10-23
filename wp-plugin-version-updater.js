// Reads and writes the `Version:` line in the plugin header.
// Group 1 keeps the key and its padding, so the header stays aligned.
const versionRegex = /^(\s*\*\s*Version:[ \t]*)(\d+\.\d+\.\d+)/m;

module.exports.readVersion = function ( contents ) {
	const found = contents.match( versionRegex );
	if ( ! found ) {
		throw new Error( 'No "Version:" line found in the plugin header.' );
	}
	return found[ 2 ];
};

module.exports.writeVersion = function ( contents, version ) {
	return contents.replace(
		versionRegex,
		( _match, prefix ) => prefix + version
	);
};
