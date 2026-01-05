# Write your code for G015_CHEFOR here
def solve(arr):
    # Your code goes here
    return bool(sum([1 for x in arr if x < 0]))
