# Write your code for G012_SUMOF here
def solve(start, end):
    # Your code goes here
    
    return sum([i for i in range(start, end+1) if i%2 == 0])
