# Write your code for G013_COUELE here
def solve(arr):
    avg = sum(arr)/len(arr)
    
    return sum([1 for x in arr if x > avg])
