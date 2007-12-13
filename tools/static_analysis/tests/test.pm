package test;

sub new
{
	my $proto = shift;
	my $class = ref($proto) || $proto;
	my $self = {};
	bless ($self, $class);
	return $self;
}

sub printInformation
{
	my ($self) = @_;
	print "\tNo information has been provided for this test\n";
}

sub runTest
{
	my ($self, $ci_root, $file) = @_;
	
	return 0;
}

sub printError
{
	my ($self, $file, $line, $error) = @_;
	print STDERR "$file\t: $line\t- $error\n";
}

1;

