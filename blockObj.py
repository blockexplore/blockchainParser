class blockObj:
  def __init__(self, hashTrans, magicNum, size, version,
               prevHash, merkHash, time, timeTest, bits, nonce, count):
    self.hashTrans = hashTrans
    self.magicNum = magicNum
    self.size = size
    self.version = version
    self.prevHash = prevHash
    self.merkHash = merkHash
    self.time = time
    self.timeTest = timeTest
    self.bits = bits
    self.nonce = nonce
    self.count = count
